{{-- Admin Portal Topbar --}}
<header id="admin-topbar" class="admin-topbar">
    <div class="admin-topbar-left">
        <button type="button" class="btn btn-outline btn-sm d-lg-none" id="admin-sidebar-toggle" title="Menu điều hướng" style="width: 38px; height: 38px; padding: 0; display: inline-flex; align-items: center; justify-content: center; border-radius: var(--radius-sm);">
            <i class="fas fa-bars"></i>
        </button>
        <h1 id="admin-page-title" class="admin-page-title mb-0">
            @yield('title', 'Bảng Điều Khiển & Thống Kê Kinh Doanh')
        </h1>
    </div>
    <div class="admin-topbar-actions" id="admin-topbar-actions">
        <a href="{{ route('home') }}" id="admin-btn-view-site" class="btn-view-site text-decoration-none">
            <i class="fas fa-store"></i> <span class="d-none d-sm-inline">Xem Cửa Hàng</span>
        </a>
        <div id="admin-notifications-btn" 
             style="width: 38px; height: 38px; border-radius: var(--radius-full); background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; cursor: pointer; color: var(--text-secondary);" 
             title="Thông báo hệ thống">
            <i class="far fa-bell"></i>
        </div>
    </div>
</header>
