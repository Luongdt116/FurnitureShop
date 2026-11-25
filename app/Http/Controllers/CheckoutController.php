<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Services\CartService;
use App\Services\OrderPlacementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly OrderPlacementService $orderPlacementService
    ) {
    }

    public function index(Request $request): View
    {
        $cartItems = $this->cartService->visibleItems(
            $request->user(),
            $request->session()->getId(),
            $request->session()->get('pending_guest_cart_session_id')
        );
        $subtotal = $cartItems->sum(fn (Cart $item) => ($item->product?->final_price ?? $item->product?->price ?? 0) * $item->quantity);
        $idempotencyKey = old('idempotency_key') ?: (string) Str::uuid();

        return view('storefront.checkout', compact('cartItems', 'subtotal', 'idempotencyKey'));
    }

    public function store(Request $request): RedirectResponse
    {
        $shipping = $request->validate([
            'recipient_name' => ['required', 'string', 'max:150'],
            'phone' => ['required', 'string', 'max:20'],
            'province' => ['required', 'string', 'max:100'],
            'district' => ['required', 'string', 'max:100'],
            'ward' => ['required', 'string', 'max:100'],
            'address_line' => ['required', 'string', 'max:500'],
            'note' => ['nullable', 'string', 'max:5000'],
            'payment_method' => ['required', 'in:cod,online_demo,vnpay,vietqr'],
            'idempotency_key' => ['nullable', 'string', 'max:64'],
        ]);

        $order = $this->orderPlacementService->place(
            $request->user(),
            $shipping,
            ($shipping['idempotency_key'] ?? null) ?: (string) Str::uuid(),
            $request->session()->getId(),
            $request->session()->get('pending_guest_cart_session_id')
        );

        $request->session()->forget('pending_guest_cart_session_id');
        $request->session()->forget('cart');

        $message = "Đặt hàng thành công. Mã đơn: {$order->order_code}";
        if ($order->payment_method !== 'cod' && $order->payment_expires_at) {
            $deadline = $order->payment_expires_at->copy()->timezone('Asia/Ho_Chi_Minh')->format('H:i, d/m/Y');
            $message .= " Vui lòng thanh toán trong 30 phút, trước {$deadline}.";
        }

        return redirect()->route('orders.index')->with('success', $message);
    }
}
