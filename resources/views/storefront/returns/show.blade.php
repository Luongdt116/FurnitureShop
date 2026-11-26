@extends('layouts.storefront')

@section('title', 'Theo dõi yêu cầu ' . $returnRequest->return_code . ' - AURA LIVING')

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
    $isRefundCompleted = $returnRequest->return_type === 'refund'
        && $returnRequest->status === 'completed'
        && $returnRequest->refund_amount !== null;
@endphp
<section class="orders-page section-padding" id="return-detail-page">
    <div class="container">
        <a href="{{ route('orders.returns.index', $order) }}" class="btn btn-ghost ps-0 mb-3" id="return-back-to-list">
            <i class="fas fa-arrow-left"></i> Danh sách yêu cầu
        </a>
        <div class="return-detail-card" id="return-detail-{{ $returnRequest->id }}">
            <div class="return-card-heading">
                <div>
                    <span class="section-eyebrow">Yêu cầu đổi trả/hoàn tiền</span>
                    <h1 class="section-title mb-1">{{ $returnRequest->return_code }}</h1>
                    <span class="text-muted">Đơn hàng <a href="{{ route('orders.show', $order) }}">{{ $order->order_code }}</a></span>
                </div>
                <x-badge :variant="match ($returnRequest->status) { 'completed' => 'success', 'rejected', 'cancelled' => 'danger', 'approved', 'received', 'inspected' => 'warning', default => 'gold' }" id="return-status-{{ $returnRequest->id }}">
                    {{ $returnStatusLabels[$returnRequest->status] ?? $returnRequest->status }}
                </x-badge>
            </div>

            <div class="return-detail-grid">
                <section>
                    <h2>Sản phẩm yêu cầu</h2>
                    @foreach ($returnRequest->items as $requestItem)
                        <div class="return-line-item" id="return-item-{{ $requestItem->id }}">
                            <div>
                                <strong>{{ $requestItem->orderItem?->product_name ?? 'Sản phẩm trong đơn hàng' }}</strong>
                                <span>Số lượng: {{ $requestItem->quantity }}</span>
                                @if ($requestItem->reason)<span>Lý do riêng: {{ $requestItem->reason }}</span>@endif
                            </div>
                        </div>
                    @endforeach
                </section>

                <section class="return-info-panel">
                    <h2>Thông tin xử lý</h2>
                    <p><strong>Hình thức:</strong> {{ $returnRequest->return_type === 'exchange' ? 'Đổi sản phẩm' : 'Hoàn tiền' }}</p>
                    <p><strong>Lý do:</strong> {{ $returnRequest->reason }}</p>
                    <p><strong>Ngày gửi:</strong> {{ $returnRequest->requested_at?->format('d/m/Y H:i') }}</p>
                    @if ($returnRequest->evidence_image)
                        <a href="{{ \Illuminate\Support\Facades\Storage::url($returnRequest->evidence_image) }}" target="_blank" rel="noopener" class="return-evidence-link">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($returnRequest->evidence_image) }}" alt="Ảnh bằng chứng đổi trả" class="return-evidence-image">
                            <span>Xem ảnh bằng chứng</span>
                        </a>
                    @endif
                    @if ($returnRequest->admin_note)
                        <div class="return-admin-response" id="return-admin-response">
                            <strong>Phản hồi từ cửa hàng</strong>
                            <p>{{ $returnRequest->admin_note }}</p>
                        </div>
                    @endif
                    @if ($returnRequest->reject_reason)
                        <div class="return-rejection" id="return-rejection-reason">
                            <strong>Lý do chưa thể tiếp nhận</strong>
                            <p>{{ $returnRequest->reject_reason }}</p>
                        </div>
                    @endif
                    @if ($isRefundCompleted)
                        <div class="return-refund-complete" id="return-refund-complete">
                            <strong>Đã hoàn tiền</strong>
                            <span>{{ number_format((float) $returnRequest->refund_amount, 0, ',', '.') }}₫</span>
                            @if ($returnRequest->completed_at)<small>{{ $returnRequest->completed_at->format('d/m/Y H:i') }}</small>@endif
                        </div>
                    @elseif ($returnRequest->return_type === 'refund')
                        <div class="return-refund-pending" id="return-refund-pending">
                            Yêu cầu hoàn tiền chưa hoàn tất; chưa ghi nhận là đã hoàn tiền.
                        </div>
                    @endif
                </section>
            </div>
        </div>
    </div>
</section>
@endsection
