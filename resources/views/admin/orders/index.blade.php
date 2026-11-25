@extends('layouts.admin')

@section('title', 'Quản lý đơn hàng')

@section('content')
@php
    $statusVariants = [
        'preparing' => 'gold',
        'awaiting_shipment' => 'warning',
        'shipping' => 'warning',
        'delivered' => 'success',
        'cancelled' => 'danger',
    ];
@endphp
<section class="admin-orders-page" id="admin-order-page">
    <div class="admin-page-heading">
        <div>
            <span class="section-eyebrow">Bán hàng & dịch vụ</span>
            <h1 class="admin-page-title">Quản lý đơn hàng</h1>
            <p class="text-muted mb-0">Tìm đơn, đối soát thanh toán và theo dõi tình trạng vận chuyển.</p>
        </div>
    </div>

    <div class="admin-card mb-4" id="admin-order-filters-card">
        <form action="{{ route('admin.orders.index') }}" method="GET" class="admin-order-filter-form" id="admin-order-filter-form">
            <div class="admin-filter-field admin-filter-search">
                <label for="admin-order-search">Tìm kiếm</label>
                <input type="search" name="q" id="admin-order-search" class="form-control form-input" value="{{ $filters['q'] ?? '' }}" placeholder="Mã đơn, tên, email, số điện thoại">
            </div>
            <div class="admin-filter-field">
                <label for="admin-order-status-filter">Trạng thái đơn</label>
                <select name="status" id="admin-order-status-filter" class="form-select form-input">
                    <option value="">Tất cả trạng thái</option>
                    @foreach ($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-field">
                <label for="admin-order-payment-filter">Thanh toán</label>
                <select name="payment_status" id="admin-order-payment-filter" class="form-select form-input">
                    <option value="">Tất cả</option>
                    @foreach (['pending' => 'Đang chờ', 'paid' => 'Đã thanh toán', 'failed' => 'Thất bại', 'refunded' => 'Đã hoàn'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['payment_status'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="admin-filter-field">
                <label for="admin-order-date-from">Từ ngày</label>
                <input type="date" name="from" id="admin-order-date-from" class="form-control form-input" value="{{ $filters['from'] ?? '' }}">
            </div>
            <div class="admin-filter-field">
                <label for="admin-order-date-to">Đến ngày</label>
                <input type="date" name="to" id="admin-order-date-to" class="form-control form-input" value="{{ $filters['to'] ?? '' }}">
            </div>
            <div class="admin-order-filter-actions">
                <button type="submit" class="btn btn-primary"><i class="fas fa-magnifying-glass"></i> Lọc đơn</button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-ghost">Xóa lọc</a>
            </div>
        </form>
    </div>

    <div class="admin-card" id="admin-order-table-card">
        <div class="table-toolbar">
            <div><strong>{{ $orders->total() }}</strong> đơn hàng</div>
            <span class="text-muted small">Trang {{ $orders->currentPage() }} / {{ max(1, $orders->lastPage()) }}</span>
        </div>
        <div class="data-table-wrap">
            <table class="data-table" id="admin-order-table">
                <thead>
                    <tr>
                        <th>Mã đơn / Ngày</th>
                        <th>Khách hàng</th>
                        <th>Sản phẩm</th>
                        <th>Thanh toán</th>
                        <th>Trạng thái</th>
                        <th class="text-end">Tổng tiền</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($orders as $order)
                        <tr id="admin-order-row-{{ $order->id }}">
                            <td><strong>{{ $order->order_code }}</strong><small class="d-block text-muted">{{ $order->created_at?->format('d/m/Y H:i') }}</small></td>
                            <td>{{ $order->recipient_name }}<small class="d-block text-muted">{{ $order->phone }}</small></td>
                            <td>{{ $order->items_count }}</td>
                            <td>
                                <span>{{ $order->payment_method === 'cod' ? 'COD' : strtoupper($order->payment_method) }}</span>
                                <small class="d-block text-muted">{{ ['pending' => 'Đang chờ', 'paid' => 'Đã thanh toán', 'failed' => 'Thất bại', 'refunded' => 'Đã hoàn'][$order->payment_status] ?? $order->payment_status }}</small>
                            </td>
                            <td><x-badge :variant="$statusVariants[$order->status] ?? 'primary'">{{ $statusLabels[$order->status] ?? $order->status }}</x-badge></td>
                            <td class="text-end fw-bold">{{ number_format((float) $order->total, 0, ',', '.') }}₫</td>
                            <td class="text-end"><a href="{{ route('admin.orders.show', $order) }}" class="btn btn-outline btn-sm" id="admin-order-open-{{ $order->id }}">Chi tiết</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center py-5 text-muted">Không tìm thấy đơn hàng phù hợp.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="admin-table-pagination" id="admin-order-pagination">{{ $orders->links('pagination::bootstrap-5') }}</div>
        @endif
    </div>
</section>
@endsection
