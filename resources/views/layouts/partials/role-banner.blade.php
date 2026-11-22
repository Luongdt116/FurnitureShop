{{-- Demo Role & Mode Switcher Banner (Aura Living Standard) --}}
<div id="store-role-switch-banner" class="role-switch-banner">
    <div class="d-flex align-items-center gap-2">
        <span class="badge badge-gold" style="font-size: 0.75rem;">AURA LIVING (High-Fi)</span>
        <span class="d-none d-md-inline text-white-50">|</span>
        <span>Hệ Thống Trưng Bày Đồ Nội Thất &bull; 
            @auth
                Đang đăng nhập: <strong id="store-role-indicator" class="text-white">{{ Auth::user()->name }} ({{ Auth::user()->role === 'admin' ? 'Quản Trị Viên' : 'Khách Hàng' }})</strong>
            @else
                Đang duyệt: <strong id="store-role-indicator" class="text-white">Khách vãng lai (Guest)</strong>
            @endauth
        </span>
    </div>
    <div class="d-flex align-items-center gap-3">
        @if(auth()->check() && auth()->user()->role === 'admin')
            <a id="store-admin-portal-link" href="{{ route('admin.dashboard') }}" class="role-switch-btn">
                <i class="fas fa-user-shield"></i> Cổng Quản Trị (Admin Portal) &rarr;
            </a>
        @else
            <a id="store-admin-portal-link" href="{{ route('admin.dashboard') }}" class="role-switch-btn" title="Xem Cổng Quản Trị (Demo)">
                <i class="fas fa-user-shield"></i> Cổng Quản Trị (Admin) &rarr;
            </a>
        @endif
        <span class="text-white-50 d-none d-sm-inline">|</span>
        <span class="link-wireframe-view d-none d-md-inline-flex">
            <i class="fas fa-gem text-gold"></i> Bản Chuẩn High-Fidelity
        </span>
    </div>
</div>
