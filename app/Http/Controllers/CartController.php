<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private readonly CartService $cartService)
    {
    }

    public function index(Request $request): View
    {
        $cartItems = $this->items($request);
        $subtotal = $cartItems->sum(fn (Cart $item) => ($item->product?->final_price ?? $item->product?->price ?? 0) * $item->quantity);

        return view('storefront.cart', compact('cartItems', 'subtotal'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_id' => ['required', 'integer', 'min:1'],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:999'],
            'color' => ['nullable', 'string', 'max:100'],
        ]);

        $this->cartService->add(
            $request->user(),
            $request->session()->getId(),
            $request->session()->get('pending_guest_cart_session_id'),
            (int) $data['product_id'],
            (int) ($data['quantity'] ?? 1)
        );

        if ($request->boolean('buy_now')) {
            return redirect()->route('checkout.index');
        }

        $mergeErrors = $this->retryPendingMerge($request);
        if ($mergeErrors) {
            return back()->withErrors($mergeErrors)->with('warning', 'Giỏ hàng khách vẫn được giữ lại. Hãy xử lý sản phẩm báo lỗi để hoàn tất gộp giỏ.');
        }

        return back()->with('success', 'Đã thêm sản phẩm vào giỏ hàng.')->with('open_cart', true);
    }

    public function update(Request $request, int $cart): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:999']]);
        $this->cartService->updateQuantity(
            $request->user(),
            $request->session()->getId(),
            $request->session()->get('pending_guest_cart_session_id'),
            $cart,
            (int) $data['quantity']
        );

        $mergeErrors = $this->retryPendingMerge($request);
        if ($mergeErrors) {
            return back()->withErrors($mergeErrors)->with('warning', 'Giỏ hàng khách vẫn được giữ lại. Hãy xử lý sản phẩm báo lỗi để hoàn tất gộp giỏ.');
        }

        return back()->with('success', 'Đã cập nhật số lượng sản phẩm.');
    }

    public function updateProduct(Request $request, int $product): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1', 'max:999']]);
        $this->cartService->updateProductQuantity(
            $request->user(),
            $request->session()->getId(),
            $request->session()->get('pending_guest_cart_session_id'),
            $product,
            (int) $data['quantity']
        );

        $mergeErrors = $this->retryPendingMerge($request);
        if ($mergeErrors) {
            return back()->withErrors($mergeErrors)->with('warning', 'Giỏ hàng khách vẫn được giữ lại. Hãy xử lý sản phẩm báo lỗi để hoàn tất gộp giỏ.');
        }

        return back()->with('success', 'Đã cập nhật số lượng sản phẩm.')->with('open_cart', true);
    }

    public function destroy(Request $request, int $cart): RedirectResponse
    {
        $this->cartService->deleteItem(
            $request->user(),
            $request->session()->getId(),
            $request->session()->get('pending_guest_cart_session_id'),
            $cart
        );

        $mergeErrors = $this->retryPendingMerge($request);
        if ($mergeErrors) {
            return back()->withErrors($mergeErrors)->with('warning', 'Giỏ hàng khách vẫn được giữ lại. Hãy xử lý sản phẩm báo lỗi để hoàn tất gộp giỏ.');
        }

        return back()->with('info', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    public function remove(Request $request, int $id): RedirectResponse
    {
        $this->cartService->deleteProduct(
            $request->user(),
            $request->session()->getId(),
            $request->session()->get('pending_guest_cart_session_id'),
            $id
        );

        $mergeErrors = $this->retryPendingMerge($request);
        if ($mergeErrors) {
            return back()->withErrors($mergeErrors)->with('warning', 'Giỏ hàng khách vẫn được giữ lại. Hãy xử lý sản phẩm báo lỗi để hoàn tất gộp giỏ.');
        }

        return back()->with('info', 'Đã xóa sản phẩm khỏi giỏ hàng.')->with('open_cart', true);
    }

    public function clear(Request $request): RedirectResponse
    {
        $this->cartService->clear(
            $request->user(),
            $request->session()->getId(),
            $request->session()->get('pending_guest_cart_session_id')
        );

        $request->session()->forget('cart');
        $request->session()->forget('pending_guest_cart_session_id');

        return back()->with('info', 'Đã làm trống giỏ hàng.')->with('open_cart', true);
    }

    private function items(Request $request): Collection
    {
        return $this->cartService->visibleItems(
            $request->user(),
            $request->session()->getId(),
            $request->session()->get('pending_guest_cart_session_id')
        );
    }

    private function retryPendingMerge(Request $request): ?array
    {
        $guestSessionId = $request->session()->get('pending_guest_cart_session_id');
        if (!$request->user() || !$guestSessionId) {
            return null;
        }

        try {
            $this->cartService->mergeGuestCart($request->user(), $guestSessionId);
            $request->session()->forget('pending_guest_cart_session_id');

            return null;
        } catch (ValidationException $exception) {
            return $exception->errors();
        }
    }
}
