{{-- Reusable Flash Messages Component --}}
@if (session('success'))
    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="background-color: var(--status-success-bg); color: var(--status-success); border-radius: var(--radius-sm);">
        <i class="fas fa-circle-check fs-5"></i>
        <div class="flex-grow-1">
            <strong>Thành công!</strong> {{ session('success') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="background-color: var(--status-danger-bg); color: var(--status-danger); border-radius: var(--radius-sm);">
        <i class="fas fa-circle-exclamation fs-5"></i>
        <div class="flex-grow-1">
            <strong>Lỗi!</strong> {{ session('error') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="background-color: var(--status-warning-bg); color: #b45309; border-radius: var(--radius-sm);">
        <i class="fas fa-triangle-exclamation fs-5"></i>
        <div class="flex-grow-1">
            <strong>Lưu ý!</strong> {{ session('warning') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if (session('info'))
    <div class="alert alert-info alert-dismissible fade show d-flex align-items-center gap-2 mb-4 border-0 shadow-sm" role="alert" style="background-color: var(--status-info-bg); color: var(--status-info); border-radius: var(--radius-sm);">
        <i class="fas fa-circle-info fs-5"></i>
        <div class="flex-grow-1">
            <strong>Thông tin:</strong> {{ session('info') }}
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4 border-0 shadow-sm" role="alert" style="background-color: var(--status-danger-bg); color: var(--status-danger); border-radius: var(--radius-sm);">
        <div class="d-flex align-items-center gap-2 mb-2">
            <i class="fas fa-circle-xmark fs-5"></i>
            <strong>Vui lòng kiểm tra lại thông tin:</strong>
        </div>
        <ul class="mb-0 ps-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif
