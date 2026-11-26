<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\CancellationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class OrderCancellationController extends Controller
{
    public function customer(Request $request, int $id, CancellationService $cancellations): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);
        $cancelled = $cancellations->cancelByCustomer($id, $request->user(), $data['reason']);

        return back()->with($cancelled ? 'success' : 'error', $cancelled
            ? 'Đơn hàng đã được hủy.'
            : 'Đơn hàng không thể hủy ở trạng thái hiện tại.');
    }

    public function admin(Request $request, int $id, CancellationService $cancellations): RedirectResponse
    {
        $data = $request->validate(['reason' => ['required', 'string', 'max:255']]);
        $cancelled = $cancellations->cancelByAdmin($id, $request->user(), $data['reason']);

        return back()->with($cancelled ? 'success' : 'error', $cancelled
            ? 'Đơn hàng đã được hủy.'
            : 'Đơn hàng không thể hủy ở trạng thái hiện tại.');
    }

    public function expireCustomer(Request $request, int $id, CancellationService $cancellations): RedirectResponse
    {
        $order = Order::query()->whereKey($id)->firstOrFail();
        abort_unless($order->user_id === $request->user()->id, 404);

        $cancelled = $cancellations->expireOnlinePayment($id);

        return back()->with($cancelled ? 'success' : 'error', $cancelled
            ? 'Đơn hàng đã hết hạn thanh toán và được hệ thống hủy.'
            : 'Đơn hàng đã được thanh toán hoặc không còn đủ điều kiện tự hủy.');
    }
}
