@php
    $cartSession = session('cart', []);
    $cartSubtotal = array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cartSession));
    $cartItemCount = array_sum(array_column($cartSession, 'quantity'));
    $freeShippingTarget = 3000000;
    $freeShippingPercent = min(100, $cartSubtotal > 0 ? round(($cartSubtotal / $freeShippingTarget) * 100) : 0);
@endphp

{{-- Slide-Out Cart Drawer --}}
<div id="store-cart-drawer-overlay" class="cart-drawer-overlay">
    <div class="cart-drawer" id="store-cart-drawer">
        {{-- Header --}}
        <div class="cart-drawer-header">
            <div class="cart-drawer-title">
                <i class="fas fa-shopping-bag" style="color: var(--accent);"></i> Giỏ hàng của bạn ({{ $cartItemCount }})
            </div>
            <button type="button" id="store-cart-close-btn" class="modal-close" onclick="toggleCartDrawer(false)" aria-label="Đóng giỏ hàng">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Free Shipping Progress --}}
        <div class="free-shipping-bar-wrap">
            <div id="store-free-shipping-text" class="free-shipping-text">
                @if($cartSubtotal >= $freeShippingTarget)
                    <span class="text-success fw-bold"><i class="fas fa-check-circle me-1"></i> Đã đủ điều kiện MIỄN PHÍ VẬN CHUYỂN!</span>
                @else
                    Mua thêm <strong>{{ number_format($freeShippingTarget - $cartSubtotal, 0, ',', '.') }}₫</strong> để nhận Miễn phí vận chuyển
                @endif
            </div>
            <div class="free-shipping-track">
                <div id="store-free-shipping-progress" class="free-shipping-progress" style="width: {{ $freeShippingPercent }}%;"></div>
            </div>
        </div>

        {{-- Cart Items Body --}}
        <div id="store-cart-drawer-body" class="cart-drawer-body">
            @if(empty($cartSession))
                <div class="text-center py-5 text-muted">
                    <i class="fas fa-shopping-bag fa-3x mb-3 text-secondary opacity-50"></i>
                    <p class="mb-2">Giỏ hàng của bạn đang trống</p>
                    <a href="{{ route('catalog.index') }}" class="btn btn-outline btn-sm mt-2" onclick="toggleCartDrawer(false)">
                        Khám phá sản phẩm &rarr;
                    </a>
                </div>
            @else
                <div class="d-flex flex-column gap-3 p-3">
                    @foreach($cartSession as $item)
                        <div class="d-flex align-items-center gap-3 p-2 rounded-3 border bg-white position-relative shadow-xs">
                            <img src="{{ $item['image'] }}" alt="{{ $item['name'] }}" 
                                 style="width: 60px; height: 60px; object-fit: cover; border-radius: var(--radius-sm);" class="flex-shrink-0">
                            
                            <div class="flex-grow-1 overflow-hidden">
                                <a href="{{ route('product.detail', $item['slug']) }}" class="text-decoration-none text-reset fw-semibold d-block text-truncate" style="font-size: 0.88rem;" title="{{ $item['name'] }}">
                                    {{ $item['name'] }}
                                </a>
                                @if(!empty($item['color']))
                                    <div class="text-muted" style="font-size: 0.75rem;">Màu: {{ $item['color'] }}</div>
                                @endif
                                <div class="d-flex align-items-center justify-content-between mt-1">
                                    <span style="font-size: 0.85rem; color: var(--accent); font-weight: 700;">
                                        {{ number_format($item['price'], 0, ',', '.') }}₫ <span class="text-muted fw-normal" style="font-size: 0.75rem;">x {{ $item['quantity'] }}</span>
                                    </span>
                                </div>
                                <form action="{{ route('cart.product.update', $item['id']) }}" method="POST" class="d-flex align-items-center gap-2 mt-2">
                                    @csrf
                                    @method('PATCH')
                                    <div class="cart-drawer-quantity" data-cart-quantity>
                                        <button type="button" aria-label="Giảm số lượng" data-cart-quantity-step="-1">−</button>
                                        <input type="number" name="quantity" min="1" max="{{ max(1, $item['stock']) }}" value="{{ $item['quantity'] }}" aria-label="Số lượng {{ $item['name'] }}" required>
                                        <button type="button" aria-label="Tăng số lượng" data-cart-quantity-step="1">+</button>
                                    </div>
                                    <button type="submit" class="btn btn-outline btn-sm">Cập nhật</button>
                                </form>
                            </div>

                            <form action="{{ route('cart.remove', $item['id']) }}" method="POST" class="m-0 p-0 flex-shrink-0">
                                @csrf
                                <button type="submit" class="btn btn-sm text-danger p-1 border-0" title="Xóa khỏi giỏ hàng" style="background: none; font-size: 0.85rem;">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Footer --}}
        <div id="store-cart-drawer-footer" class="cart-drawer-footer">
            <div class="cart-subtotal-row">
                <span>Tạm tính tiền hàng:</span>
                <strong id="store-cart-drawer-subtotal" style="color: var(--primary); font-size: 1.15rem;">{{ number_format($cartSubtotal, 0, ',', '.') }}₫</strong>
            </div>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 16px;">
                * Đã bao gồm hỗ trợ giao hàng & lắp đặt tận nhà.
            </p>
            @if(!empty($cartSession))
                <div class="d-flex flex-column gap-2">
                    <a href="{{ route('checkout.index') }}" id="store-checkout-btn" class="btn btn-accent btn-lg w-100">
                        Tiến Hành Đặt Hàng <i class="fas fa-arrow-right ms-2"></i>
                    </a>
                    <form action="{{ route('cart.clear') }}" method="POST" class="m-0 p-0 text-center">
                        @csrf
                        <button type="submit" class="btn btn-link btn-sm text-muted text-decoration-none" style="font-size: 0.8rem;">
                            <i class="fas fa-trash me-1"></i> Xóa tất cả trong giỏ
                        </button>
                    </form>
                </div>
            @else
                <button type="button" class="btn btn-secondary btn-lg w-100 opacity-50" disabled>
                    Giỏ hàng trống
                </button>
            @endif
        </div>
    </div>
</div>
