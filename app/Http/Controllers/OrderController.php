<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CancellationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
                'payments' => fn ($query) => $query->latest(),
                'statusHistories.changedBy',
            ])
            ->whereKey($order->getKey())
            ->firstOrFail();

        return view('storefront.orders.show', compact('order'));
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

        if (! class_exists(CancellationService::class)) {
            return back()->with('error', 'Chức năng hủy đơn đang được kết nối với dịch vụ xử lý. Vui lòng thử lại sau.');
        }

        // The service owns cancellation eligibility, status history, payment handling, and stock effects.
        app(CancellationService::class)->cancel($order, $validated['reason'], $request->user(), 'customer');

        return redirect()
            ->route('orders.show', $order)
            ->with('success', 'Yêu cầu hủy đơn hàng đã được xử lý.');
    }
}
