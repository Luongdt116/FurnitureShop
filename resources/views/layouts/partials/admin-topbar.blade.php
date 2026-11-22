{{-- Admin Portal Topbar --}}
<header id="admin-topbar" class="admin-topbar">
    <div class="admin-topbar-left">
        <h1 id="admin-page-title" class="admin-page-title">
            @yield('title', 'Bảng Điều Khiển & Thống Kê Kinh Doanh')
        </h1>
    </div>
    <div class="admin-topbar-actions" id="admin-topbar-actions">
        <a href="{{ route('home') }}" id="admin-btn-view-site" class="btn-view-site text-decoration-none">
            <i class="fas fa-store"></i> Xem Cửa Hàng (Storefront)
        </a>
        <div id="admin-notifications-btn" 
             style="width: 38px; height: 38px; border-radius: var(--radius-full); background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-secondary);" 
             title="Thông báo hệ thống">
            <i class="far fa-bell"></i>
        </div>
    </div>
</header>
