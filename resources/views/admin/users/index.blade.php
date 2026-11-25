@extends('layouts.admin')

@section('title', 'Quản lý người dùng')

@section('content')
<section class="admin-users-page" id="admin-user-page">
    <div class="admin-page-heading">
        <div>
            <span class="section-eyebrow">Khách hàng & phân quyền</span>
            <h1 class="admin-page-title">Quản lý người dùng</h1>
            <p class="text-muted mb-0">Tìm tài khoản và cập nhật vai trò, trạng thái theo dữ liệu hệ thống.</p>
        </div>
    </div>

    <div class="admin-card mb-4" id="admin-user-filter-card">
        <form action="{{ route('admin.users.index') }}" method="GET" class="admin-user-filter-form" id="admin-user-filter-form">
            <div class="admin-filter-field admin-filter-search">
                <label for="admin-user-search">Tìm kiếm</label>
                <input type="search" name="q" id="admin-user-search" class="form-control form-input" value="{{ $filters['q'] ?? '' }}" placeholder="Tên, email hoặc số điện thoại">
            </div>
            <div class="admin-filter-field">
                <label for="admin-user-role-filter">Vai trò</label>
                <select name="role" id="admin-user-role-filter" class="form-select form-input">
                    <option value="">Tất cả</option>
                    <option value="customer" @selected(($filters['role'] ?? '') === 'customer')>Khách hàng</option>
                    <option value="admin" @selected(($filters['role'] ?? '') === 'admin')>Quản trị viên</option>
                </select>
            </div>
            <div class="admin-filter-field">
                <label for="admin-user-status-filter">Trạng thái</label>
                <select name="status" id="admin-user-status-filter" class="form-select form-input">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(($filters['status'] ?? '') === 'active')>Đang hoạt động</option>
                    <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>Đã khóa</option>
                </select>
            </div>
            <div class="admin-user-filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-magnifying-glass"></i> Lọc</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Xóa lọc</a>
            </div>
        </form>
    </div>

    <div class="admin-card" id="admin-user-table-card">
        <div class="table-toolbar"><strong>{{ $users->total() }} tài khoản</strong></div>
        <div class="data-table-wrap">
            <table class="data-table" id="admin-user-table">
                <thead>
                    <tr><th>Người dùng</th><th>Liên hệ</th><th>Đơn hàng</th><th>Vai trò</th><th>Trạng thái</th><th>Cập nhật quyền</th></tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr id="admin-user-row-{{ $user->id }}">
                            <td><strong>{{ $user->name }}</strong><small class="d-block text-muted">Tham gia {{ $user->created_at?->format('d/m/Y') }}</small></td>
                            <td>{{ $user->email }}<small class="d-block text-muted">{{ $user->phone ?: 'Chưa có số điện thoại' }}</small></td>
                            <td>{{ $user->orders_count }}</td>
                            <td><x-badge :variant="$user->role === 'admin' ? 'gold' : 'primary'">{{ $user->role === 'admin' ? 'Quản trị viên' : 'Khách hàng' }}</x-badge></td>
                            <td><x-badge :variant="$user->is_active ? 'success' : 'danger'">{{ $user->is_active ? 'Đang hoạt động' : 'Đã khóa' }}</x-badge></td>
                            <td>
                                <form action="{{ route('admin.users.update', $user) }}" method="POST" class="admin-user-update-form" id="admin-user-form-{{ $user->id }}">
                                    @csrf
                                    @method('PATCH')
                                    <label for="admin-user-role-{{ $user->id }}" class="visually-hidden">Vai trò của {{ $user->name }}</label>
                                    <select name="role" id="admin-user-role-{{ $user->id }}" class="form-select form-input form-input-sm" required>
                                        <option value="customer" @selected($user->role === 'customer')>Khách hàng</option>
                                        <option value="admin" @selected($user->role === 'admin')>Quản trị viên</option>
                                    </select>
                                    <label class="admin-user-active-toggle" for="admin-user-active-{{ $user->id }}">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" id="admin-user-active-{{ $user->id }}" @checked($user->is_active)>
                                        Hoạt động
                                    </label>
                                    <button type="submit" class="btn btn-outline btn-sm" id="admin-user-save-{{ $user->id }}">Lưu</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-muted">Không tìm thấy tài khoản phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($users->hasPages())
            <div class="admin-table-pagination" id="admin-user-pagination">{{ $users->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</section>
@endsection
