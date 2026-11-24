<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AURA LIVING - Tuyệt Tác Nội Thất Bắc Âu & Japandi Tinh Tế')</title>
    <meta name="description" content="@yield('meta_description', 'Thương hiệu nội thất cao cấp AURA LIVING: Sofa da bò Ý, bàn ăn gỗ sồi tự nhiên, giường ngủ Bắc Âu. Bảo hành 5 năm, miễn phí vận chuyển.')">

    {{-- Google Fonts --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;800&family=Playfair+Display:ital,wght@0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    {{-- Font Awesome 6 CDN fallback --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    {{-- Vite CSS & JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')
</head>
<body id="store-body">

    {{-- 1. Demo Role & Mode Switcher Banner --}}
    @include('layouts.partials.role-banner')

    {{-- 2. Storefront Announcement Bar & Header --}}
    @include('layouts.partials.storefront-header')

    {{-- 3. Flash Messages Wrapper --}}
    <div class="container mt-3">
        <x-flash-message />
    </div>

    {{-- 4. Main View Content --}}
    <main id="store-main-content">
        @yield('content')
    </main>

    {{-- 5. Storefront Footer --}}
    @include('layouts.partials.storefront-footer')

    {{-- 6. Slide-Out Cart Drawer --}}
    @include('layouts.partials.cart-drawer')

    {{-- 7. Modals: Login & Register (Reusable components) --}}
    <x-modal id="store-login-modal" title="Đăng Nhập Tài Khoản" size="sm">
        <div style="background: var(--bg-subtle); padding: 12px; border-radius: var(--radius-sm); margin-bottom: 20px;">
            <div style="font-size: 0.78rem; font-weight: 700; color: var(--accent); text-transform: uppercase; margin-bottom: 6px;">
                <i class="fas fa-bolt"></i> Đăng nhập nhanh bằng tài khoản mẫu:
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline btn-sm flex-fill" onclick="quickFillLogin('customer')">
                    Khách Hàng (VIP)
                </button>
                <button type="button" class="btn btn-primary btn-sm flex-fill" onclick="quickFillLogin('admin')">
                    Admin Quản Trị
                </button>
            </div>
        </div>

        <form action="{{ route('login') }}" method="POST" id="store-login-form">
            @csrf
            <div class="form-group mb-3">
                <label for="login-email" class="form-label">Email hoặc Số điện thoại <span class="required text-danger">*</span></label>
                <input id="login-email" name="email" class="form-control form-input" type="email" required placeholder="name@example.com" value="{{ old('email', 'customer@auraliving.vn') }}">
            </div>
            <div class="form-group mb-3">
                <div class="d-flex justify-content-between align-items-center">
                    <label for="login-password" class="form-label mb-0">Mật khẩu <span class="required text-danger">*</span></label>
                    <a href="javascript:void(0)" onclick="openForgotModal()" style="font-size: 0.8rem; color: var(--accent);">Quên mật khẩu?</a>
                </div>
                <input id="login-password" name="password" class="form-control form-input mt-1" type="password" required value="password">
            </div>
            <label class="d-flex align-items-center gap-2 mb-4" style="font-size: 0.85rem; cursor: pointer;">
                <input type="checkbox" name="remember" checked> Ghi nhớ đăng nhập 30 ngày (Remember Me)
            </label>
            <button type="submit" class="btn btn-primary btn-lg w-100">
                Đăng Nhập
            </button>
            <div class="text-center mt-3" style="font-size: 0.88rem;">
                Chưa có tài khoản? <a href="javascript:void(0)" onclick="openRegisterModal()" style="color: var(--accent); font-weight: 700;">Đăng ký ngay</a>
            </div>
        </form>
    </x-modal>

    <x-modal id="store-register-modal" title="Đăng Ký Tài Khoản" size="md">
        <form action="{{ route('register') }}" method="POST" id="store-register-form">
            @csrf
            <div class="form-group mb-3">
                <label for="reg-name" class="form-label">Họ và tên <span class="required text-danger">*</span></label>
                <input id="reg-name" name="name" class="form-control form-input" type="text" required placeholder="Nguyễn Văn A" value="{{ old('name') }}">
            </div>
            <div class="form-group mb-3">
                <label for="reg-email" class="form-label">Email <span class="required text-danger">*</span></label>
                <input id="reg-email" name="email" class="form-control form-input" type="email" required placeholder="email@gmail.com" value="{{ old('email') }}">
            </div>
            <div class="form-group mb-3">
                <label for="reg-phone" class="form-label">Số điện thoại (10 chữ số) <span class="required text-danger">*</span></label>
                <input id="reg-phone" name="phone" class="form-control form-input" type="text" required placeholder="0912345678" value="{{ old('phone') }}">
            </div>
            <div class="form-group mb-3">
                <label for="reg-pass" class="form-label">Mật khẩu (Tối thiểu 8 ký tự) <span class="required text-danger">*</span></label>
                <input id="reg-pass" name="password" class="form-control form-input" type="password" required>
            </div>
            <div class="form-group mb-4">
                <label for="reg-pass-confirm" class="form-label">Xác nhận mật khẩu <span class="required text-danger">*</span></label>
                <input id="reg-pass-confirm" name="password_confirmation" class="form-control form-input" type="password" required>
            </div>
            <button type="submit" class="btn btn-primary btn-lg w-100">
                Đăng Ký Thành Viên
            </button>
        </form>
    </x-modal>

    {{-- 8. Toast Notifications Container --}}
    <x-toast />

    {{-- 9. Base UI Scripts --}}
    <script>
        function toggleUserDropdown() {
            const menu = document.getElementById('store-user-dropdown-menu');
            if (menu) menu.classList.toggle('show');
        }

        function toggleCartDrawer(open) {
            const overlay = document.getElementById('store-cart-drawer-overlay');
            if (overlay) {
                if (open) overlay.classList.add('active');
                else overlay.classList.remove('active');
            }
        }

        function openLoginModal() {
            closeAuraModal('store-register-modal');
            const modal = document.getElementById('store-login-modal');
            if (modal) modal.classList.add('active');
            const menu = document.getElementById('store-user-dropdown-menu');
            if (menu) menu.classList.remove('show');
        }

        function openRegisterModal() {
            closeAuraModal('store-login-modal');
            const modal = document.getElementById('store-register-modal');
            if (modal) modal.classList.add('active');
            const menu = document.getElementById('store-user-dropdown-menu');
            if (menu) menu.classList.remove('show');
        }

        function closeAuraModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) modal.classList.remove('active');
        }

        function quickFillLogin(role) {
            const emailInput = document.getElementById('login-email');
            const passInput = document.getElementById('login-password');
            if (role === 'admin') {
                if (emailInput) emailInput.value = 'admin@auraliving.vn';
                if (passInput) passInput.value = 'password';
            } else {
                if (emailInput) emailInput.value = 'customer@auraliving.vn';
                if (passInput) passInput.value = 'password';
            }
        }

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            const userMenuWrapper = document.getElementById('store-user-menu-wrapper');
            const userDropdown = document.getElementById('store-user-dropdown-menu');
            if (userMenuWrapper && userDropdown && !userMenuWrapper.contains(e.target)) {
                userDropdown.classList.remove('show');
            }

            const cartOverlay = document.getElementById('store-cart-drawer-overlay');
            if (cartOverlay && e.target === cartOverlay) {
                cartOverlay.classList.remove('active');
            }

            const searchInput = document.getElementById('store-header-search-input');
            const suggestionsBox = document.getElementById('store-search-suggestions');
            if (searchInput && suggestionsBox && !searchInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                suggestionsBox.style.display = 'none';
            }
        });

        // Live Search Suggestions Dropdown
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('store-header-search-input');
            const suggestionsBox = document.getElementById('store-search-suggestions');
            let searchTimeout = null;

            if (searchInput && suggestionsBox) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(searchTimeout);
                    const query = this.value.trim();
                    if (query.length < 2) {
                        suggestionsBox.style.display = 'none';
                        suggestionsBox.innerHTML = '';
                        return;
                    }

                    searchTimeout = setTimeout(function() {
                        fetch(`{{ route('catalog.suggestions') }}?q=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(items => {
                                if (!items || items.length === 0) {
                                    suggestionsBox.innerHTML = `
                                        <div class="suggestion-header">Gợi ý sản phẩm</div>
                                        <div class="p-2 text-muted small">Không tìm thấy sản phẩm phù hợp</div>
                                    `;
                                } else {
                                    let html = `<div class="suggestion-header">Sản phẩm gợi ý (${items.length})</div>`;
                                    items.forEach(item => {
                                        html += `
                                            <div class="suggestion-item" onclick="window.location.href='${item.url}'">
                                                <img src="${item.image}" alt="${item.name}">
                                                <div class="flex-grow-1">
                                                    <div class="suggestion-name">${item.name}</div>
                                                    <div class="suggestion-price">${item.price}</div>
                                                </div>
                                            </div>
                                        `;
                                    });
                                    suggestionsBox.innerHTML = html;
                                }
                                suggestionsBox.style.display = 'block';
                            })
                            .catch(() => {
                                suggestionsBox.style.display = 'none';
                            });
                    }, 250);
                });
            }

            // Auto open cart drawer if requested by action
            @if(session('open_cart'))
                setTimeout(function() {
                    toggleCartDrawer(true);
                }, 250);
            @endif
        });
    </script>

    @stack('scripts')
</body>
</html>
