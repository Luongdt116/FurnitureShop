@extends('layouts.storefront')

@section('title', 'Đổi trả đơn hàng ' . $order->order_code . ' - AURA LIVING')

@section('content')
@php
    $returnStatusLabels = [
        'requested' => 'Đang chờ xử lý',
        'approved' => 'Đã duyệt, chờ nhận hàng',
        'received' => 'Đã tiếp nhận hàng',
        'inspected' => 'Đã kiểm tra hàng',
        'rejected' => 'Không được chấp nhận',
        'completed' => 'Đã hoàn tất',
        'cancelled' => 'Đã hủy yêu cầu',
    ];
@endphp
<section class="orders-page section-padding" id="return-list-page">
    <div class="container">
        <a href="{{ route('orders.show', $order) }}" class="btn btn-ghost ps-0 mb-3" id="return-back-to-order">
            <i class="fas fa-arrow-left"></i> Quay lại đơn hàng
        </a>
        <div class="section-heading mb-4">
            <span class="section-eyebrow">Đổi trả và hoàn tiền</span>
            <h1 class="section-title mb-2" id="return-list-title">Yêu cầu của đơn {{ $order->order_code }}</h1>
        </div>

        @forelse ($returnRequests as $returnRequest)
            <article class="return-card" id="return-card-{{ $returnRequest->id }}">
                <div class="return-card-heading">
                    <div>
                        <strong>{{ $returnRequest->return_code }}</strong>
                        <span>Gửi ngày {{ $returnRequest->requested_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    <x-badge :variant="match ($returnRequest->status) { 'completed' => 'success', 'rejected', 'cancelled' => 'danger', 'approved', 'received', 'inspected' => 'warning', default => 'gold' }">
                        {{ $returnStatusLabels[$returnRequest->status] ?? $returnRequest->status }}
                    </x-badge>
                </div>
                <p class="mb-2"><strong>Hình thức:</strong> {{ $returnRequest->return_type === 'exchange' ? 'Đổi sản phẩm' : 'Hoàn tiền' }}</p>
                <p class="mb-3">{{ $returnRequest->reason }}</p>
                <a href="{{ route('orders.returns.show', [$order, $returnRequest]) }}" class="btn btn-outline btn-sm" id="return-detail-{{ $returnRequest->id }}">Xem tiến độ</a>
            </article>
        @empty
            <div class="orders-empty-state" id="return-empty-state">
                <div class="orders-empty-icon"><i class="fas fa-rotate-left"></i></div>
                <h2>Chưa có yêu cầu đổi trả</h2>
                <p class="text-muted">Bạn có thể tạo yêu cầu mới từ chi tiết đơn hàng đã giao thành công.</p>
                <a href="{{ route('orders.show', $order) }}" class="btn btn-accent">Mở chi tiết đơn hàng</a>
            </div>
        @endforelse

        @if ($returnRequests->hasPages())
            <div class="orders-pagination" id="return-pagination">{{ $returnRequests->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</section>
@endsection
