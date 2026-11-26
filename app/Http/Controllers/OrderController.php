<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CancellationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        $statusLabels = [
            'all' => 'Tất cả',
            'preparing' => 'Đang chuẩn bị',
            'awaiting_shipment' => 'Đang chờ vận chuyển',
            'shipping' => 'Đang giao',
            'delivered' => 'Đã giao thành công',
            'cancelled' => 'Đã hủy',
        ];

        $status = $request->query('status', 'all');
        if (! array_key_exists($status, $statusLabels)) {
            $status = 'all';
        }

        $orders = $request->user()
            ->orders()
            ->with(['items', 'payments'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(8)
            ->withQueryString();

        return view('storefront.orders.index', compact('orders', 'status', 'statusLabels'));
    }

    public function show(Request $request, Order $order): View
    {
        $order = $request->user()
            ->orders()
            ->with([
                'items.product',
                'items.review',
                'payments' => fn ($query) => $query->latest(),
                'statusHistories.changedBy',
                'returnRequests.items.orderItem',
            ])
            ->whereKey($order->getKey())
            ->firstOrFail();

        $alreadyRequested = DB::table('return_request_items')
            ->join('return_requests', 'return_requests.id', '=', 'return_request_items.return_request_id')
            ->where('return_requests.order_id', $order->id)
            ->whereNotIn('return_requests.status', ['rejected', 'cancelled'])
            ->select('return_request_items.order_item_id', DB::raw('SUM(return_request_items.quantity) as requested_quantity'))
            ->groupBy('return_request_items.order_item_id')
            ->pluck('requested_quantity', 'order_item_id');

        $returnableQuantities = $order->items->mapWithKeys(fn ($item) => [
            $item->id => max(0, $item->quantity - (int) ($alreadyRequested[$item->id] ?? 0)),
        ]);

        return view('storefront.orders.show', compact('order', 'returnableQuantities'));
    }

    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'max:255'],
        ], [
            'reason.required' => 'Vui lòng nhập lý do hủy đơn hàng.',
            'reason.max' => 'Lý do hủy không được vượt quá 255 ký tự.',
        ]);

        $order = $request->user()
            ->orders()
            ->whereKey($order->getKey())
            ->firstOrFail();

        // The service owns cancellation eligibility, status history, and pending payment handling.
        app(CancellationService::class)->cancel($order, $validated['reason'], $request->user(), 'customer');

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Yêu cầu hủy đơn hàng đã được xử lý.');
    }
}
