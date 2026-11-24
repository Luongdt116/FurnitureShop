{{-- Storefront Top Announcement Bar --}}
<div id="store-announcement-bar" class="announcement-bar">
    <div class="container announcement-inner">
        <div>
            <span class="announcement-badge"><i class="fas fa-truck-fast"></i> ƯU ĐÃI THÁNG:</span>
            Miễn phí vận chuyển & hỗ trợ lắp đặt trọn gói toàn quốc cho đơn hàng từ 3.000.000₫
        </div>
        <div class="announcement-links">
            <a href="{{ route('orders.index') ?? '#' }}" id="store-lookup-order-link">
                <i class="fas fa-map-location-dot"></i> Tra cứu đơn hàng
            </a>
            <a href="tel:18006868" id="store-hotline-link">
                <i class="fas fa-headset"></i> Hotline: 1800 6868
            </a>
        </div>
    </div>
</div>

{{-- Main Storefront Header --}}
<header id="store-main-header" class="header">
    <div class="container header-main">
        {{-- Brand Logo --}}
        <a href="{{ route('home') }}" class="logo" id="store-logo-link">
            <div class="logo-icon"><i class="fas fa-couch"></i></div>
            <div>
                <span class="logo-text">AURA LIVING</span>
                <span class="logo-tagline">Nghệ thuật không gian sống</span>
            </div>
        </a>

        {{-- Main Navigation Menu --}}
        <nav>
            <ul class="nav-menu mb-0 ps-0" id="store-nav-menu">
                <li><a href="{{ route('home') }}" class="nav-link {{ request()->routeIs('home') ? 'active' : '' }}">Trang Chủ</a></li>
                <li><a href="{{ route('catalog.index') }}" class="nav-link {{ request()->routeIs('catalog.*') ? 'active' : '' }}">Sản Phẩm</a></li>
                <li><a href="{{ route('catalog.index', ['room' => 'Phòng Khách']) }}" class="nav-link {{ request('room') === 'Phòng Khách' ? 'active' : '' }}">Phòng Khách</a></li>
                <li><a href="{{ route('catalog.index', ['room' => 'Phòng Ăn']) }}" class="nav-link {{ request('room') === 'Phòng Ăn' ? 'active' : '' }}">Phòng Ăn</a></li>
                <li><a href="{{ route('catalog.index', ['room' => 'Phòng Ngủ']) }}" class="nav-link {{ request('room') === 'Phòng Ngủ' ? 'active' : '' }}">Phòng Ngủ</a></li>
                @auth
                    <li><a href="{{ route('account.profile') }}" class="nav-link {{ request()->routeIs('account.*') ? 'active' : '' }}">Tài Khoản & Đơn Mua</a></li>
                @else
                    <li><a href="{{ url('/login') }}" class="nav-link">Đơn Mua & Tài Khoản</a></li>
                @endauth
            </ul>
        </nav>

        {{-- Live Search Bar --}}
        <div id="store-header-search-wrapper" class="header-search">
            <form action="{{ route('catalog.search') }}" method="GET" class="search-wrapper" id="store-search-form">
                <input id="store-header-search-input" name="q" class="search-input" type="text" 
                       placeholder="Tìm sofa da, bàn ăn gỗ sồi, giường ngủ..." 
                       value="{{ request('q') }}" autocomplete="off">
                <button type="submit" class="search-btn" id="store-search-btn" aria-label="Tìm kiếm">
                    <i class="fas fa-search"></i>
                </button>
            </form>
            {{-- Dynamic Suggestions Dropdown --}}
            <div id="store-search-suggestions" class="search-suggestions"></div>
        </div>

        {{-- Header Actions (Cart & User Account) --}}
        <div class="header-actions" id="store-header-actions">
            {{-- Cart Drawer Trigger --}}
            <button type="button" id="store-cart-toggle-btn" class="action-btn" title="Giỏ hàng" onclick="toggleCartDrawer(true)">
                <i class="fas fa-shopping-bag"></i>
                <span id="store-cart-badge-count" class="cart-badge">{{ array_sum(array_column(session('cart', []), 'quantity')) }}</span>
            </button>

            {{-- User Dropdown Menu --}}
            <div class="user-menu-wrapper" id="store-user-menu-wrapper">
                <button type="button" id="store-user-menu-btn" class="action-btn" title="Tài khoản" onclick="toggleUserDropdown()">
                    <i class="far fa-user"></i>
                </button>
                <div id="store-user-dropdown-menu" class="user-dropdown">
                    @auth
                        <div class="user-dropdown-header">
                            <div class="user-name-display">
                                <span id="store-user-dropdown-name">{{ Auth::user()->name }}</span>
                                @if(Auth::user()->role === 'admin')
                                    <span class="badge badge-gold" style="font-size: 0.65rem;">ADMIN</span>
                                @else
                                    <span class="badge badge-vip" style="font-size: 0.65rem;">VIP</span>
                                @endif
                            </div>
                            <div id="store-user-dropdown-email" class="user-email-display">{{ Auth::user()->email }}</div>
                        </div>

                        <div id="store-dropdown-logged-in-items">
                            <a href="{{ route('account.profile') }}" class="dropdown-link">
                                <i class="fas fa-id-card"></i> Hồ sơ cá nhân
                            </a>
                            <a href="{{ route('orders.index') }}" class="dropdown-link">
                                <i class="fas fa-box"></i> Đơn hàng của tôi
                            </a>
                            <a href="{{ route('account.addresses') }}" class="dropdown-link">
                                <i class="fas fa-map-marked-alt"></i> Sổ địa chỉ
                            </a>
                            @if(Auth::user()->role === 'admin')
                                <a href="{{ route('admin.dashboard') }}" class="dropdown-link" style="color: #b37d36; font-weight: 700; border-top: 1px dashed var(--border-color); margin-top: 6px; padding-top: 8px;">
                                    <i class="fas fa-user-shield"></i> Cổng Quản Trị Hệ Thống (Admin)
                                </a>
                            @endif
                            <form action="{{ route('logout') }}" method="POST" id="store-logout-form" class="m-0 p-0">
                                @csrf
                                <button type="submit" class="dropdown-link w-100 text-start border-0 bg-transparent" style="color: var(--status-danger); border-top: 1px solid var(--border-color); margin-top: 6px; padding-top: 10px;">
                                    <i class="fas fa-sign-out-alt"></i> Đăng xuất
                                </button>
                            </form>
                        </div>
                    @else
                        <div class="user-dropdown-header">
                            <div class="user-name-display">Tài khoản AURA</div>
                            <div class="user-email-display">Đăng nhập để nhận ưu đãi VIP</div>
                        </div>
                        <div id="store-dropdown-guest-items">
                            <a href="{{ url('/login') }}" class="dropdown-link">
                                <i class="fas fa-sign-in-alt"></i> Đăng nhập
                            </a>
                            <a href="{{ url('/register') }}" class="dropdown-link">
                                <i class="fas fa-user-plus"></i> Đăng ký tài khoản
                            </a>
                        </div>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</header>
