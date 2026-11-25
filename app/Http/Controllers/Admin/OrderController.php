<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderStatusHistory;
use App\Models\ReturnRequest;
use App\Services\CancellationService;
use App\Services\ReturnInventoryService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class OrderController extends Controller
{
    private const STATUS_LABELS = [
        'preparing' => 'Đang chuẩn bị',
        'awaiting_shipment' => 'Đang chờ vận chuyển',
        'shipping' => 'Đang giao',
        'delivered' => 'Đã giao thành công',
        'cancelled' => 'Đã hủy',
    ];

    private const TRANSITIONS = [
        'preparing' => ['awaiting_shipment'],
        'awaiting_shipment' => ['shipping'],
        'shipping' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
    ];

    private const RETURN_TRANSITIONS = [
        'requested' => ['approved', 'rejected'],
        'approved' => ['received', 'rejected'],
        'received' => ['inspected', 'rejected'],
        'inspected' => ['completed', 'rejected'],
        'completed' => [],
        'rejected' => [],
        'cancelled' => [],
    ];

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:150'],
            'status' => ['nullable', Rule::in(array_keys(self::STATUS_LABELS))],
            'payment_status' => ['nullable', 'string', 'max:30'],
            'from' => ['nullable', 'date'],
            'to' => ['nullable', 'date', 'after_or_equal:from'],
        ]);

        $orders = Order::query()
            ->with('user')
            ->withCount('items')
            ->when($filters['q'] ?? null, function ($query, string $term) {
                $query->where(function ($query) use ($term) {
                    $query->where('order_code', 'like', "%{$term}%")
                        ->orWhere('recipient_name', 'like', "%{$term}%")
                        ->orWhere('phone', 'like', "%{$term}%")
                        ->orWhereHas('user', fn ($userQuery) => $userQuery->where('email', 'like', "%{$term}%"));
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['payment_status'] ?? null, fn ($query, string $status) => $query->where('payment_status', $status))
            ->when($filters['from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.orders.index', [
            'orders' => $orders,
            'filters' => $filters,
            'statusLabels' => self::STATUS_LABELS,
        ]);
    }

    public function show(Order $order): View
    {
        $order->load([
            'user',
            'items.product',
            'payments' => fn ($query) => $query->latest(),
            'statusHistories.changedBy',
            'returnRequests.user',
            'returnRequests.items.orderItem',
        ]);

        return view('admin.orders.show', [
            'order' => $order,
            'statusLabels' => self::STATUS_LABELS,
            'nextStatuses' => self::TRANSITIONS[$order->status] ?? [],
            'returnTransitions' => self::RETURN_TRANSITIONS,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(self::STATUS_LABELS))],
            'reason' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($order, $validated, $request) {
            $lockedOrder = Order::query()->whereKey($order->getKey())->lockForUpdate()->firstOrFail();
            $allowed = self::TRANSITIONS[$lockedOrder->status] ?? [];

            if (! in_array($validated['status'], $allowed, true)) {
                throw ValidationException::withMessages([
                    'status' => 'Trạng thái này không thể chuyển từ trạng thái hiện tại.',
                ]);
            }

            $fromStatus = $lockedOrder->status;
            $lockedOrder->status = $validated['status'];
            if ($validated['status'] === 'awaiting_shipment' && ! $lockedOrder->confirmed_at) {
                $lockedOrder->confirmed_at = now();
            }
            if ($validated['status'] === 'delivered') {
                $lockedOrder->completed_at = now();
            }
            $lockedOrder->save();

            OrderStatusHistory::create([
                'order_id' => $lockedOrder->id,
                'from_status' => $fromStatus,
                'to_status' => $validated['status'],
                'changed_by' => $request->user()->id,
                'actor_type' => 'admin',
                'reason' => $validated['reason'] ?? null,
            ]);
        });

        return back()->with('success', 'Đã cập nhật trạng thái đơn hàng và lưu lịch sử.');
    }

    public function updateTracking(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'shipping_carrier' => ['nullable', 'string', 'max:50'],
            'tracking_code' => ['nullable', 'string', 'max:100'],
            'estimated_delivery_date' => ['nullable', 'date'],
        ]);

        if ($order->status === 'cancelled') {
            throw ValidationException::withMessages([
                'tracking_code' => 'Không thể cập nhật vận chuyển cho đơn đã hủy.',
            ]);
        }

        $order->fill($validated)->save();

        return back()->with('success', 'Đã cập nhật thông tin vận chuyển.');
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'reason.required' => 'Vui lòng nhập lý do hủy đơn hàng.',
        ]);

        app(CancellationService::class)->cancel($order, $validated['reason'], $request->user(), 'admin');

        return back()->with('success', 'Đã gửi lệnh hủy đơn qua dịch vụ xử lý.');
    }

    public function updateReturn(Request $request, ReturnRequest $returnRequest): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_merge(...array_values(self::RETURN_TRANSITIONS)))],
            'admin_note' => ['nullable', 'string', 'max:5000'],
            'reject_reason' => ['required_if:status,rejected', 'nullable', 'string', 'max:5000'],
            'refund_amount' => ['nullable', 'numeric', 'min:0'],
        ], [
            'reject_reason.required_if' => 'Vui lòng ghi lý do từ chối yêu cầu.',
            'refund_amount.required_if' => 'Yêu cầu hoàn tiền hoàn tất cần có số tiền hoàn.',
        ]);

        DB::transaction(function () use ($returnRequest, $validated, $request) {
            $lockedRequest = ReturnRequest::query()->whereKey($returnRequest->getKey())->lockForUpdate()->firstOrFail();
            $allowed = self::RETURN_TRANSITIONS[$lockedRequest->status] ?? [];

            if (! in_array($validated['status'], $allowed, true)) {
                throw ValidationException::withMessages([
                    'status' => 'Luồng xử lý đổi/trả không cho phép bước trạng thái này.',
                ]);
            }

            if ($validated['status'] === 'completed' && $lockedRequest->return_type === 'refund' && ($validated['refund_amount'] ?? null) === null) {
                throw ValidationException::withMessages([
                    'refund_amount' => 'Yêu cầu hoàn tiền hoàn tất cần có số tiền hoàn.',
                ]);
            }

            if ($validated['status'] === 'completed') {
                app(ReturnInventoryService::class)->restockInspectedReturn($lockedRequest, $request->user());
            }

            $lockedRequest->status = $validated['status'];
            $lockedRequest->admin_note = $validated['admin_note'] ?? null;
            $lockedRequest->reject_reason = $validated['status'] === 'rejected' ? $validated['reject_reason'] : null;

            if ($validated['status'] === 'approved') {
                $lockedRequest->approved_at = now();
            }
            if ($validated['status'] === 'completed') {
                $lockedRequest->completed_at = now();
                $lockedRequest->refund_amount = $lockedRequest->return_type === 'refund'
                    ? $validated['refund_amount']
                    : null;
            }

            $lockedRequest->save();
        });

        return back()->with('success', 'Đã hoàn tất yêu cầu đổi/trả và ghi nhận hàng đã kiểm tra vào kho.');
    }
}
