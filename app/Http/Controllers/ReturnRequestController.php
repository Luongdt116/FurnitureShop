<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ReturnRequest as CustomerReturnRequest;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ReturnRequestController extends Controller
{
    public function index(Request $request, Order $order): View
    {
        $order = $request->user()->orders()->whereKey($order->getKey())->firstOrFail();
        $returnRequests = $order->returnRequests()
            ->where('user_id', $request->user()->id)
            ->with('items.orderItem')
            ->latest('requested_at')
            ->paginate(10);

        return view('storefront.returns.index', compact('order', 'returnRequests'));
    }

    public function show(Request $request, Order $order, CustomerReturnRequest $returnRequest): View
    {
        $order = $request->user()->orders()->whereKey($order->getKey())->firstOrFail();
        $returnRequest = $order->returnRequests()
            ->where('user_id', $request->user()->id)
            ->whereKey($returnRequest->getKey())
            ->with('items.orderItem')
            ->firstOrFail();

        return view('storefront.returns.show', compact('order', 'returnRequest'));
    }

    public function store(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'return_type' => ['required', Rule::in(['exchange', 'refund'])],
            'reason' => ['required', 'string', 'max:5000'],
            'evidence' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.order_item_id' => ['required', 'integer', 'distinct'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
            'items.*.reason' => ['nullable', 'string', 'max:255'],
        ], [
            'reason.required' => 'Vui lòng nhập lý do đổi/trả.',
            'items.required' => 'Vui lòng chọn ít nhất một sản phẩm.',
            'items.min' => 'Vui lòng chọn ít nhất một sản phẩm.',
            'items.*.quantity.min' => 'Số lượng yêu cầu phải ít nhất là 1.',
            'evidence.image' => 'Ảnh bằng chứng phải là tệp hình ảnh hợp lệ.',
        ]);

        $user = $request->user();
        $storedEvidence = null;
        $selectedLines = collect($validated['items'])
            ->filter(fn (array $line) => (int) $line['quantity'] > 0)
            ->values();

        if ($selectedLines->isEmpty()) {
            throw ValidationException::withMessages([
                'items' => 'Chọn ít nhất một sản phẩm và số lượng cần đổi/trả.',
            ]);
        }

        try {
            $createdRequest = DB::transaction(function () use ($request, $order, $validated, $selectedLines, $user, &$storedEvidence) {
                $ownedOrder = $user->orders()
                    ->whereKey($order->getKey())
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($ownedOrder->status !== 'delivered') {
                    throw ValidationException::withMessages([
                        'order' => 'Chỉ có thể tạo yêu cầu đổi/trả cho đơn hàng đã giao thành công.',
                    ]);
                }

                $requestedLines = $selectedLines->keyBy(fn (array $line) => (int) $line['order_item_id']);
                $orderItems = $ownedOrder->items()
                    ->whereIn('id', $requestedLines->keys())
                    ->lockForUpdate()
                    ->get()
                    ->keyBy('id');

                if ($orderItems->count() !== $requestedLines->count()) {
                    throw ValidationException::withMessages([
                        'items' => 'Có sản phẩm không thuộc đơn hàng này.',
                    ]);
                }

                foreach ($orderItems as $orderItem) {
                    $requestedQuantity = (int) $requestedLines[$orderItem->id]['quantity'];
                    $alreadyRequested = (int) DB::table('return_request_items')
                        ->join('return_requests', 'return_requests.id', '=', 'return_request_items.return_request_id')
                        ->where('return_requests.order_id', $ownedOrder->id)
                        ->where('return_request_items.order_item_id', $orderItem->id)
                        ->whereNotIn('return_requests.status', ['rejected', 'cancelled'])
                        ->sum('return_request_items.quantity');

                    if ($requestedQuantity + $alreadyRequested > $orderItem->quantity) {
                        throw ValidationException::withMessages([
                            'items' => "Số lượng yêu cầu cho {$orderItem->product_name} vượt quá số lượng còn được đổi/trả.",
                        ]);
                    }
                }

                if ($request->hasFile('evidence')) {
                    $storedEvidence = $request->file('evidence')->store('returns/evidence', 'public');
                }

                $returnRequest = $ownedOrder->returnRequests()->create([
                    'return_code' => 'RTN-' . now()->format('Ymd') . '-' . Str::upper(Str::random(6)),
                    'user_id' => $user->id,
                    'return_type' => $validated['return_type'],
                    'status' => 'requested',
                    'reason' => $validated['reason'],
                    'evidence_image' => $storedEvidence,
                    'requested_at' => now(),
                ]);

                foreach ($orderItems as $orderItem) {
                    $line = $requestedLines[$orderItem->id];
                    $returnRequest->items()->create([
                        'order_item_id' => $orderItem->id,
                        'quantity' => (int) $line['quantity'],
                        'reason' => $line['reason'] ?? null,
                    ]);
                }

                return $returnRequest;
            });
        } catch (QueryException $exception) {
            if ($storedEvidence) {
                Storage::disk('public')->delete($storedEvidence);
            }

            throw $exception;
        } catch (\Throwable $exception) {
            if ($storedEvidence) {
                Storage::disk('public')->delete($storedEvidence);
            }

            throw $exception;
        }

        return redirect()
            ->route('orders.returns.show', [$order, $createdRequest])
            ->with('success', 'Yêu cầu đổi/trả đã được gửi. Bạn có thể theo dõi tiến độ tại đây.');
    }
}
