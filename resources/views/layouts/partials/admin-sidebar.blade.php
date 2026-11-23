{{-- Admin Sidebar Navigation --}}
<aside id="admin-sidebar" class="admin-sidebar">
    {{-- Header --}}
    <div class="admin-sidebar-header">
        <div class="admin-sidebar-logo-icon"><i class="fas fa-couch"></i></div>
        <div class="admin-sidebar-title">AURA ADMIN</div>
        <span class="admin-sidebar-badge">V2.6</span>
    </div>

    {{-- Menu List --}}
    <ul class="admin-sidebar-menu ps-0 mb-0" id="admin-sidebar-menu">
        <li class="admin-menu-header">TỔNG QUAN HỆ THỐNG</li>
        <li class="admin-menu-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" id="admin-nav-dashboard">
            <a href="{{ route('admin.dashboard') }}" class="d-flex align-items-center gap-3 text-decoration-none w-100 text-reset">
                <span class="menu-icon"><i class="fas fa-chart-pie"></i></span>
                <span>Bảng điều khiển</span>
            </a>
        </li>

        <li class="admin-menu-header">QUẢN TRỊ NỘI THẤT</li>
        <li class="admin-menu-item {{ request()->routeIs('admin.products.*') ? 'active' : '' }}" id="admin-nav-products">
            <a href="{{ route('admin.products.index') ?? '#' }}" class="d-flex align-items-center gap-3 text-decoration-none w-100 text-reset">
                <span class="menu-icon"><i class="fas fa-boxes-stacked"></i></span>
                <span>Sản phẩm</span>
            </a>
        </li>
        <li class="admin-menu-item {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" id="admin-nav-categories">
            <a href="{{ route('admin.categories.index') ?? '#' }}" class="d-flex align-items-center gap-3 text-decoration-none w-100 text-reset">
                <span class="menu-icon"><i class="fas fa-tags"></i></span>
                <span>Danh mục</span>
            </a>
        </li>

        <li class="admin-menu-header">BÁN HÀNG & DỊCH VỤ</li>
        <li class="admin-menu-item {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}" id="admin-nav-orders">
            <a href="{{ route('admin.orders.index') ?? '#' }}" class="d-flex align-items-center gap-3 text-decoration-none w-100 text-reset">
                <span class="menu-icon"><i class="fas fa-receipt"></i></span>
                <span>Đơn hàng & Đổi trả</span>
            </a>
        </li>
        <li class="admin-menu-item {{ request()->routeIs('admin.vouchers.*') ? 'active' : '' }}" id="admin-nav-vouchers">
            <a href="{{ route('admin.vouchers.index') ?? '#' }}" class="d-flex align-items-center gap-3 text-decoration-none w-100 text-reset">
                <span class="menu-icon"><i class="fas fa-ticket-simple"></i></span>
                <span>Mã giảm giá</span>
            </a>
        </li>

        <li class="admin-menu-header">KHÁCH HÀNG & PHÂN TÍCH</li>
        <li class="admin-menu-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" id="admin-nav-users">
            <a href="{{ route('admin.users.index') ?? '#' }}" class="d-flex align-items-center gap-3 text-decoration-none w-100 text-reset">
                <span class="menu-icon"><i class="fas fa-users"></i></span>
                <span>Thành viên</span>
            </a>
        </li>
        <li class="admin-menu-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" id="admin-nav-reports">
            <a href="{{ route('admin.reports.revenue') ?? route('admin.dashboard') }}" class="d-flex align-items-center gap-3 text-decoration-none w-100 text-reset">
                <span class="menu-icon"><i class="fas fa-file-invoice-dollar"></i></span>
                <span>Báo cáo doanh số</span>
            </a>
        </li>
    </ul>

    {{-- Footer --}}
    <div class="admin-sidebar-footer" id="admin-sidebar-footer">
        <img class="admin-user-thumb" src="{{ Auth::user()->avatar ?? 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?auto=format&fit=crop&w=200&q=80' }}" alt="Admin Avatar">
        <div class="admin-user-info">
            <div class="admin-user-name">{{ Auth::user()->name ?? 'Quản Trị Viên' }}</div>
            <div class="admin-user-role">Quản Trị Viên Tối Cao</div>
        </div>
        <form action="{{ route('logout') }}" method="POST" class="d-inline m-0 p-0" id="admin-logout-form">
            @csrf
            <button type="submit" class="btn btn-ghost text-secondary p-0 border-0" title="Đăng xuất" style="color: #a89f95;">
                <i class="fas fa-power-off"></i>
            </button>
        </form>
    </div>
</aside>
