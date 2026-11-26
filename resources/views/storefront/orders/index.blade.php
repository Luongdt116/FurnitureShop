@extends('layouts.storefront')

@section('title', 'Đơn hàng của tôi - AURA LIVING')

@section('content')
<section class="orders-page section-padding" id="orders-page">
    <div class="container">
        <div class="section-heading d-flex flex-wrap align-items-end justify-content-between gap-3 mb-4">
            <div>
                <span class="section-eyebrow">Tài khoản của bạn</span>
                <h1 class="section-title mb-2" id="orders-title">Đơn hàng của tôi</h1>
                <p class="text-muted mb-0">Theo dõi sản phẩm, thanh toán và tình trạng giao hàng của bạn.</p>
            </div>
            <a href="{{ route('catalog.index') }}" class="btn btn-outline" id="orders-continue-shopping">
                <i class="fas fa-arrow-left"></i> Tiếp tục mua sắm
            </a>
        </div>

        <nav class="order-tabs-nav" aria-label="Lọc trạng thái đơn hàng" id="orders-status-filters">
            @foreach ($statusLabels as $value => $label)
                <a href="{{ route('orders.index', ['status' => $value]) }}"
                   class="order-tab-btn {{ $status === $value ? 'active' : '' }}"
                   @if ($status === $value) aria-current="page" @endif
                   id="orders-filter-{{ $value }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        @forelse ($orders as $order)
            @php
                $statusLabel = $statusLabels[$order->status] ?? $order->status;
                $statusVariant = match ($order->status) {
                    'delivered' => 'success',
                    'cancelled' => 'danger',
                    'shipping', 'awaiting_shipment' => 'warning',
                    default => 'gold',
                };
            @endphp
            <article class="order-card" id="orders-card-{{ $order->id }}">
                <header class="order-card-header">
                    <div class="order-meta-info">
                        <span class="order-id-tag">Mã đơn <strong>{{ $order->order_code }}</strong></span>
                        <span>Đặt ngày {{ $order->created_at?->format('d/m/Y H:i') }}</span>
                    </div>
                    <x-badge :variant="$statusVariant" id="orders-status-{{ $order->id }}">
                        {{ $statusLabel }}
                    </x-badge>
                </header>

                <div class="order-items-body">
                    @foreach ($order->items as $item)
                        <div class="order-item-row" id="orders-item-{{ $item->id }}">
                            @if ($item->product_image)
                                @php
                                    $image = $item->product_image;
                                    $imageUrl = \Illuminate\Support\Str::startsWith($image, ['http://', 'https://', '/'])
                                        ? $image
                                        : \Illuminate\Support\Facades\Storage::url($image);
                                @endphp
                                <img src="{{ $imageUrl }}" alt="{{ $item->product_name }}" class="order-item-thumb" loading="lazy">
                            @else
                                <div class="order-item-thumb order-item-thumb-empty" aria-hidden="true"><i class="fas fa-couch"></i></div>
                            @endif
                            <div class="order-item-summary">
                                <h2 class="order-item-title">{{ $item->product_name }}</h2>
                                @if ($item->sku)<small class="text-muted">Mã SP: {{ $item->sku }}</small>@endif
                                <div class="text-muted small">Số lượng: {{ $item->quantity }}</div>
                            </div>
                            <div class="order-item-price">
                                <span class="text-muted small">{{ number_format((float) $item->unit_price, 0, ',', '.') }}₫ / sản phẩm</span>
                                <strong>{{ number_format((float) $item->line_total, 0, ',', '.') }}₫</strong>
                            </div>
                        </div>
                    @endforeach
                </div>

                <footer class="order-card-footer">
                    <div class="order-total-display">
                        <span class="text-muted">{{ $order->items->sum('quantity') }} sản phẩm · {{ $order->payment_method === 'cod' ? 'Thanh toán khi nhận hàng' : 'Thanh toán online' }}</span>
                        <strong>{{ number_format((float) $order->total, 0, ',', '.') }}₫</strong>
                    </div>
                    <div class="order-actions-wrap">
                        <a href="{{ route('orders.show', $order) }}" class="btn btn-primary btn-sm" id="orders-detail-{{ $order->id }}">
                            Xem chi tiết <i class="fas fa-arrow-right"></i>
                        </a>
                    </div>
                </footer>
            </article>
        @empty
            <div class="orders-empty-state" id="orders-empty-state">
                <div class="orders-empty-icon"><i class="fas fa-box-open"></i></div>
                <h2>{{ $status === 'all' ? 'Bạn chưa có đơn hàng nào' : 'Không có đơn hàng ở trạng thái này' }}</h2>
                <p class="text-muted">Các đơn hàng của bạn sẽ xuất hiện tại đây sau khi đặt hàng.</p>
                <a href="{{ route('catalog.index') }}" class="btn btn-accent">Khám phá sản phẩm</a>
            </div>
        @endforelse

        @if ($orders->hasPages())
            <div class="orders-pagination" id="orders-pagination">{{ $orders->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</section>
@endsection
