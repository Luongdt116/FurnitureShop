@extends('layouts.admin')

@section('title', $title ?? 'Quản Trị Phân Hệ')

@section('content')
<div class="admin-card p-5 text-center my-4 shadow-sm" id="admin-module-placeholder">
    <div style="width: 80px; height: 80px; margin: 0 auto 20px; border-radius: var(--radius-full); background: var(--bg-subtle); display: flex; align-items: center; justify-content: center; font-size: 2.2rem; color: var(--accent);">
        <i class="{{ $icon ?? 'fas fa-cubes' }}"></i>
    </div>
    <h3 class="h4 font-heading mb-2 text-primary">{{ $title ?? 'Phân Hệ Quản Trị' }}</h3>
    <p class="text-muted" style="max-width: 540px; margin: 0 auto 24px; line-height: 1.6;">
        {{ $description ?? 'Phân hệ chức năng này đang được phát triển song song bởi thành viên trong nhóm. Giao diện nền tảng Aura Admin đã sẵn sàng tích hợp.' }}
    </p>
    <div class="d-flex justify-content-center align-items-center gap-3">
        <span class="badge badge-gold px-3 py-2" style="font-size: 0.8rem;">
            <i class="fas fa-user-gear me-1"></i> Phụ trách: {{ $assignedTo ?? 'Thành viên nhóm' }}
        </span>
        <a href="{{ route('admin.dashboard') }}" class="btn btn-outline btn-sm">
            <i class="fas fa-arrow-left me-1"></i> Quay lại Bảng điều khiển
        </a>
    </div>
</div>
@endsection
