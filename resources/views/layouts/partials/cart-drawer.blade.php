{{-- Slide-Out Cart Drawer --}}
<div id="store-cart-drawer-overlay" class="cart-drawer-overlay">
    <div class="cart-drawer" id="store-cart-drawer">
        {{-- Header --}}
        <div class="cart-drawer-header">
            <div class="cart-drawer-title">
                <i class="fas fa-shopping-bag" style="color: var(--accent);"></i> Giỏ hàng của bạn
            </div>
            <button type="button" id="store-cart-close-btn" class="modal-close" onclick="toggleCartDrawer(false)" aria-label="Đóng giỏ hàng">
                <i class="fas fa-times"></i>
            </button>
        </div>

        {{-- Free Shipping Progress --}}
        <div class="free-shipping-bar-wrap">
            <div id="store-free-shipping-text" class="free-shipping-text">Mua thêm để nhận Miễn phí vận chuyển</div>
            <div class="free-shipping-track">
                <div id="store-free-shipping-progress" class="free-shipping-progress" style="width: 0%;"></div>
            </div>
        </div>

        {{-- Cart Items Body --}}
        <div id="store-cart-drawer-body" class="cart-drawer-body">
            <div class="text-center py-5 text-muted">
                <i class="fas fa-shopping-bag fa-3x mb-3 text-secondary opacity-50"></i>
                <p>Giỏ hàng của bạn đang trống</p>
                <a href="{{ route('catalog.index') }}" class="btn btn-outline btn-sm mt-2">
                    Khám phá sản phẩm &rarr;
                </a>
            </div>
        </div>

        {{-- Footer --}}
        <div id="store-cart-drawer-footer" class="cart-drawer-footer">
            <div class="cart-subtotal-row">
                <span>Tạm tính tiền hàng:</span>
                <strong id="store-cart-drawer-subtotal" style="color: var(--primary); font-size: 1.15rem;">0₫</strong>
            </div>
            <p style="font-size: 0.78rem; color: var(--text-muted); margin-bottom: 16px;">
                * Chưa bao gồm mã giảm giá voucher (sẽ áp dụng tại bước thanh toán).
            </p>
            <a href="{{ route('checkout.index') }}" id="store-checkout-btn" class="btn btn-accent btn-lg w-100">
                Tiến Hành Đặt Hàng <i class="fas fa-arrow-right ms-2"></i>
            </a>
        </div>
    </div>
</div>
