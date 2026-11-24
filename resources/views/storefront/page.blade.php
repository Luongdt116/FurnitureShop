@extends('layouts.storefront')

@section('title', $title ?? 'AURA LIVING')

@section('content')
<div class="py-5 bg-white">
    <div class="container" style="max-width: 900px;">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0" style="font-size: 0.85rem;">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">Trang chủ</a></li>
                <li class="breadcrumb-item active text-primary fw-semibold" aria-current="page">{{ $title ?? 'Trang thông tin' }}</li>
            </ol>
        </nav>

        <div class="mb-4 pb-3 border-bottom">
            <h1 class="font-heading display-6 fw-bold text-primary mb-2">{{ $title ?? 'AURA LIVING' }}</h1>
            @if(isset($subtitle))
                <p class="text-muted lead" style="font-size: 1.05rem;">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="content-body text-secondary" style="line-height: 1.8; font-size: 1rem;">
            {!! $content ?? '' !!}
        </div>

        <div class="mt-5 pt-4 border-top d-flex gap-3">
            <a href="{{ route('catalog.index') }}" class="btn btn-accent btn-sm">
                <i class="fas fa-couch me-1"></i> Khám Phá Bộ Sưu Tập
            </a>
            <a href="{{ route('home') }}" class="btn btn-outline btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Quay Lại Trang Chủ
            </a>
        </div>
    </div>
</div>
@endsection
