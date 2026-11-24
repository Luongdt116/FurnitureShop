@extends('layouts.admin')

@section('title', 'Bảng Điều Khiển & Thống Kê Kinh Doanh')

@section('content')
<div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4" id="admin-dashboard-header">
    <div>
        <h2 class="h4 font-heading mb-1 text-primary">Tổng Quan Vận Hành & Doanh Số</h2>
        <span class="text-muted small">Cập nhật dữ liệu thời gian thực theo chuẩn kế toán (chỉ tính đơn giao thành công & đã thanh toán)</span>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.reports.revenue') }}" class="btn btn-accent btn-sm shadow-sm" id="admin-quick-report-btn">
            <i class="fas fa-file-invoice-dollar me-1"></i> Báo Cáo Doanh Thu Kế Toán &rarr;
        </a>
    </div>
</div>

{{-- 4 KPI Summary Cards --}}
<div class="kpi-grid" id="admin-kpi-grid">
    {{-- KPI 1: Net Revenue --}}
    <div class="kpi-card" id="admin-kpi-revenue">
        <div>
            <div class="kpi-label">Doanh Thu Thuần Kế Toán</div>
            <div id="admin-kpi-total-revenue" class="kpi-val">{{ number_format($kpi['net_revenue'], 0, ',', '.') }}₫</div>
            <div class="kpi-trend trend-up">
                <i class="fas fa-arrow-trend-up"></i> Gộp: {{ number_format($kpi['gross_revenue'], 0, ',', '.') }}₫
            </div>
            @if($kpi['total_refunds'] > 0)
                <div class="text-danger small mt-1" style="font-size: 0.76rem;">
                    <i class="fas fa-rotate-left"></i> Đã trừ hoàn trả: -{{ number_format($kpi['total_refunds'], 0, ',', '.') }}₫
                </div>
            @endif
        </div>
        <div class="kpi-icon-wrap"><i class="fas fa-sack-dollar"></i></div>
    </div>

    {{-- KPI 2: Total Orders --}}
    <div class="kpi-card" id="admin-kpi-orders">
        <div>
            <div class="kpi-label">Đơn Hàng Đã Giao / Tổng Đơn</div>
            <div id="admin-kpi-total-orders" class="kpi-val">
                {{ $kpi['delivered_orders_count'] }} <span style="font-size: 1rem; color: var(--text-muted); font-weight: normal;">/ {{ $kpi['total_orders_count'] }} đơn</span>
            </div>
            <div class="kpi-trend trend-up">
                <i class="fas fa-box-check"></i> Đơn huỷ: {{ $kpi['cancelled_orders_count'] }} (0₫)
            </div>
            <div class="text-muted small mt-1" style="font-size: 0.76rem;">
                AOV: ~{{ number_format($kpi['aov'], 0, ',', '.') }}₫/đơn
            </div>
        </div>
        <div class="kpi-icon-wrap" style="color: var(--status-info);"><i class="fas fa-dolly"></i></div>
    </div>

    {{-- KPI 3: Users --}}
    <div class="kpi-card" id="admin-kpi-users">
        <div>
            <div class="kpi-label">Khách Hàng Thành Viên</div>
            <div id="admin-kpi-total-users" class="kpi-val">{{ $customerCount }} người</div>
            <div class="kpi-trend trend-up">
                <i class="fas fa-user-check"></i> Tài khoản hoạt động
            </div>
            <div class="text-muted small mt-1" style="font-size: 0.76rem;">
                Trợ giá voucher: {{ number_format($kpi['voucher_discount_total'], 0, ',', '.') }}₫
            </div>
        </div>
        <div class="kpi-icon-wrap" style="color: var(--status-success);"><i class="fas fa-users"></i></div>
    </div>

    {{-- KPI 4: Return Rate --}}
    <div class="kpi-card" id="admin-kpi-returns">
        <div>
            <div class="kpi-label">Tỷ Lệ Đổi Trả / Hoàn Tiền</div>
            <div id="admin-kpi-return-rate" class="kpi-val">{{ $kpi['return_rate'] }}%</div>
            <div class="kpi-trend {{ $kpi['return_rate'] <= 5 ? 'trend-up' : 'trend-down' }}">
                <i class="fas fa-shield-halved"></i> {{ $kpi['refunded_orders_count'] }} hoàn tiền &bull; {{ $kpi['exchange_orders_count'] }} đổi hàng
            </div>
            <div class="text-muted small mt-1" style="font-size: 0.76rem;">
                Đổi hàng không trừ doanh thu
            </div>
        </div>
        <div class="kpi-icon-wrap" style="color: #8b5cf6;"><i class="fas fa-rotate-left"></i></div>
    </div>
</div>

{{-- Charts & Analytics Section --}}
<div class="charts-grid" id="admin-charts-grid">
    {{-- Left Chart: 6-Month Revenue Trend --}}
    <div class="chart-card" id="admin-chart-revenue">
        <div class="chart-header">
            <div>
                <h3 class="chart-title">Doanh Thu Thuần 6 Tháng Gần Nhất (Triệu VNĐ)</h3>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Số liệu đối soát thực tế (Gross trừ hoàn tiền)</div>
            </div>
            <span class="badge badge-gold"><i class="fas fa-circle-check"></i> Chuẩn kế toán</span>
        </div>

        {{-- Dynamic SVG Bar & Area Chart --}}
        <div style="width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch;">
            <div style="min-width: 480px; height: 220px; position: relative;">
                <svg id="admin-revenue-chart-svg" width="100%" height="100%" viewBox="0 0 540 210" preserveAspectRatio="xMidYMid meet">
                    <!-- Grid Horizontal Lines -->
                    <line x1="40" y1="20" x2="520" y2="20" stroke="#f0ede8" stroke-dasharray="3"/>
                    <line x1="40" y1="65" x2="520" y2="65" stroke="#f0ede8" stroke-dasharray="3"/>
                    <line x1="40" y1="110" x2="520" y2="110" stroke="#f0ede8" stroke-dasharray="3"/>
                    <line x1="40" y1="155" x2="520" y2="155" stroke="#e5dfd8"/>

                    <!-- Y-Axis labels -->
                    <text x="32" y="24" font-size="10" fill="#8c827a" text-anchor="end">200M</text>
                    <text x="32" y="69" font-size="10" fill="#8c827a" text-anchor="end">150M</text>
                    <text x="32" y="114" font-size="10" fill="#8c827a" text-anchor="end">100M</text>
                    <text x="32" y="159" font-size="10" fill="#8c827a" text-anchor="end">0</text>

                    @php
                        $maxVal = 200000000;
                        $points = [];
                        $barWidth = 32;
                        $chartXStart = 75;
                        $stepX = 80;
                    @endphp

                    @foreach($revenueTrend as $idx => $m)
                        @php
                            $cx = $chartXStart + ($idx * $stepX);
                            $barHeight = max(10, min(140, ($m['net_revenue'] / $maxVal) * 140));
                            $barY = 155 - $barHeight;
                            $points[] = "$cx $barY";
                        @endphp

                        <!-- Bar Background -->
                        <rect x="{{ $cx - ($barWidth / 2) }}" y="{{ $barY }}" width="{{ $barWidth }}" height="{{ $barHeight }}" 
                              rx="4" fill="var(--bg-subtle)" stroke="var(--border-color)"/>
                        
                        <!-- Bar Active Fill (Gold) -->
                        <rect x="{{ $cx - ($barWidth / 2) }}" y="{{ $barY }}" width="{{ $barWidth }}" height="{{ $barHeight }}" 
                              rx="4" fill="{{ $idx === count($revenueTrend) - 1 ? 'var(--accent)' : '#d6c5b0' }}" opacity="0.9"/>
                        
                        <!-- Value Label on Top -->
                        <text x="{{ $cx }}" y="{{ $barY - 6 }}" font-size="10" font-weight="bold" fill="var(--primary)" text-anchor="middle">
                            {{ round($m['net_revenue'] / 1000000) }}M
                        </text>

                        <!-- X-Axis Month Label -->
                        <text x="{{ $cx }}" y="180" font-size="11" fill="var(--text-secondary)" font-weight="600" text-anchor="middle">
                            {{ $m['label'] }}
                        </text>
                    @endforeach

                    <!-- Line Connect Points -->
                    <polyline points="{{ implode(' ', $points) }}" fill="none" stroke="var(--primary)" stroke-width="2" stroke-linecap="round"/>
                    @foreach($points as $pt)
                        @php [$px, $py] = explode(' ', $pt); @endphp
                        <circle cx="{{ $px }}" cy="{{ $py }}" r="4" fill="#ffffff" stroke="var(--primary)" stroke-width="2"/>
                    @endforeach
                </svg>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-2" style="font-size: 0.8rem; color: var(--text-muted);">
            <div><i class="fas fa-info-circle me-1"></i> Tháng {{ end($revenueTrend)['full_label'] ?? 'hiện tại' }} ghi nhận cao nhất: <strong>{{ number_format(end($revenueTrend)['net_revenue'] ?? 0, 0, ',', '.') }}₫</strong></div>
            <a href="{{ route('admin.reports.revenue') }}" class="text-decoration-none text-accent fw-semibold">Xem chi tiết báo cáo &rarr;</a>
        </div>
    </div>

    {{-- Right Chart: Category Share --}}
    <div class="chart-card" id="admin-chart-categories">
        <div class="chart-header">
            <h3 class="chart-title">Cơ Cấu Danh Mục</h3>
            <span class="text-muted small">Tỷ trọng</span>
        </div>
        <div class="d-flex flex-column gap-3 pt-1" id="admin-category-breakdown-list">
            @php
                $catColors = ['var(--accent)', '#3b82f6', '#10b981', '#8b5cf6', '#ec4899'];
            @endphp
            @foreach($categoryBreakdown as $cIdx => $cat)
                @php $color = $catColors[$cIdx % count($catColors)]; @endphp
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1" style="font-size: 0.86rem;">
                        <span class="fw-semibold text-truncate pe-2" style="flex: 1; min-width: 0;">{{ $cat->name }}</span>
                        <span class="flex-shrink-0 text-end">
                            <strong style="color: {{ $color }};">{{ $cat->percentage }}%</strong>
                            <span class="text-muted small">({{ number_format($cat->revenue / 1000000, 1) }}M)</span>
                        </span>
                    </div>
                    <div style="height: 7px; background: var(--bg-subtle); border-radius: var(--radius-full); overflow: hidden;">
                        <div style="width: {{ $cat->percentage }}%; height: 100%; background: {{ $color }}; border-radius: var(--radius-full);"></div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-4 p-3 rounded-3" style="background: var(--bg-subtle); font-size: 0.82rem;">
            <i class="fas fa-crown text-warning me-1"></i> Danh mục chủ lực: <strong>{{ $categoryBreakdown->first()->name ?? 'Nội thất phòng khách' }}</strong> chiếm tỷ trọng lớn nhất toàn hệ thống.
        </div>
    </div>
</div>

{{-- Stock Alerts & Quick Operations --}}
<div class="row g-4 mb-4" id="admin-middle-section">
    {{-- Low Stock Alerts --}}
    <div class="col-lg-6">
        <div class="admin-card h-100" id="admin-low-stock-card">
            <div class="table-toolbar">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-danger"><i class="fas fa-triangle-exclamation"></i> CẢNH BÁO</span>
                    <h3 class="h6 mb-0 font-heading">Sản Phẩm Sắp Hết Hàng Trong Kho</h3>
                </div>
                <a href="{{ route('admin.products.index') }}" class="btn btn-outline btn-sm">Xem kho</a>
            </div>
            <div class="p-3">
                <div class="d-flex flex-column gap-2" id="admin-low-stock-list">
                    @foreach($lowStockProducts as $lowP)
                        <div class="d-flex align-items-center justify-content-between p-2 rounded-2 border" style="background: #ffffff;">
                            <div>
                                <div class="fw-semibold text-truncate" style="font-size: 0.88rem; max-width: 280px;">
                                    {{ $lowP->name }}
                                </div>
                                <div class="text-muted" style="font-size: 0.75rem;">
                                    Mã: <code>{{ $lowP->sku }}</code> &bull; {{ $lowP->category_name ?? ($lowP->category->name ?? 'Nội thất') }}
                                </div>
                            </div>
                            <div class="text-end">
                                <span class="badge {{ $lowP->stock <= 2 ? 'badge-danger' : 'badge-warning' }}" style="font-size: 0.78rem;">
                                    Còn {{ $lowP->stock }} chiếc
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Shortcuts --}}
    <div class="col-lg-6">
        <div class="admin-card h-100 p-4" id="admin-quick-actions-card">
            <h3 class="h6 mb-3 font-heading"><i class="fas fa-bolt text-accent me-2"></i> Lối Tắt Thao Tác Nhanh Quản Trị</h3>
            <div class="row g-3">
                <div class="col-sm-6">
                    <a href="{{ route('admin.reports.revenue') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 border text-decoration-none text-reset bg-white shadow-xs hover-elevate">
                        <div class="kpi-icon-wrap" style="width: 42px; height: 42px; font-size: 1.1rem; background: var(--bg-subtle);">
                            <i class="fas fa-file-invoice-dollar text-accent"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size: 0.9rem;">Báo Cáo Doanh Thu</div>
                            <span class="text-muted small">Xuất Excel / In ấn</span>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('admin.orders.index') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 border text-decoration-none text-reset bg-white shadow-xs hover-elevate">
                        <div class="kpi-icon-wrap" style="width: 42px; height: 42px; font-size: 1.1rem; background: rgba(59, 130, 246, 0.1); color: #3b82f6;">
                            <i class="fas fa-boxes-packing"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size: 0.9rem;">Xử Lý Đơn Hàng</div>
                            <span class="text-muted small">Giao hàng & Đổi trả</span>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('admin.products.index') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 border text-decoration-none text-reset bg-white shadow-xs hover-elevate">
                        <div class="kpi-icon-wrap" style="width: 42px; height: 42px; font-size: 1.1rem; background: rgba(16, 185, 129, 0.1); color: #10b981;">
                            <i class="fas fa-couch"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size: 0.9rem;">Sản Phẩm & Kho</div>
                            <span class="text-muted small">Cập nhật giá, tồn kho</span>
                        </div>
                    </a>
                </div>
                <div class="col-sm-6">
                    <a href="{{ route('admin.vouchers.index') }}" class="d-flex align-items-center gap-3 p-3 rounded-3 border text-decoration-none text-reset bg-white shadow-xs hover-elevate">
                        <div class="kpi-icon-wrap" style="width: 42px; height: 42px; font-size: 1.1rem; background: rgba(139, 92, 246, 0.1); color: #8b5cf6;">
                            <i class="fas fa-ticket"></i>
                        </div>
                        <div>
                            <div class="fw-bold" style="font-size: 0.9rem;">Mã Giảm Giá</div>
                            <span class="text-muted small">Voucher & Khuyến mãi</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="mt-3 p-2 text-center text-muted small rounded border-dashed border">
                Hệ thống AURA ADMIN chuẩn V2.6 &bull; Đồng bộ dữ liệu MySQL thời gian thực
            </div>
        </div>
    </div>
</div>

{{-- Recent Orders Table --}}
<div class="admin-card mb-4" id="admin-recent-orders-card">
    <div class="table-toolbar">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <h3 style="font-size: 1.15rem; font-weight: 700;" class="mb-0">Đơn Hàng Gần Đây Cần Theo Dõi</h3>
                <span class="badge badge-subtle d-inline-block d-xl-none text-muted" style="font-size: 0.72rem; background: var(--bg-subtle);">
                    <i class="fas fa-arrows-left-right text-accent me-1"></i> Cuộn ngang để xem đủ 8 cột
                </span>
            </div>
            <span class="text-muted small">Cập nhật trạng thái giao hàng, đối soát thanh toán và yêu cầu đổi trả</span>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm" id="admin-btn-all-orders">
            Xem tất cả đơn hàng &rarr;
        </a>
    </div>
    <div class="data-table-wrap">
        <table class="data-table" id="admin-recent-orders-table">
            <thead>
                <tr>
                    <th style="min-width: 110px;">Mã Đơn</th>
                    <th style="min-width: 150px;">Khách Hàng</th>
                    <th style="min-width: 200px;">Sản Phẩm Tóm Tắt</th>
                    <th style="min-width: 120px;">Tổng Tiền</th>
                    <th style="min-width: 120px;">Thanh Toán</th>
                    <th style="min-width: 130px;">Trạng Thái Đơn</th>
                    <th style="min-width: 120px;">Ngày Tạo</th>
                    <th style="min-width: 140px;">Đổi Trả / Hoàn Tiền</th>
                </tr>
            </thead>
            <tbody id="admin-recent-orders-tbody">
                @forelse($recentOrders as $order)
                    @php
                        // Format status badge
                        $st = is_array($order) ? $order['status'] : $order->status;
                        $code = is_array($order) ? $order['order_code'] : $order->order_code;
                        $name = is_array($order) ? $order['recipient_name'] : ($order->recipient_name ?? $order->user?->name ?? 'Khách hàng');
                        $phone = is_array($order) ? $order['phone'] : $order->phone;
                        $total = is_array($order) ? $order['total'] : $order->total;
                        $method = is_array($order) ? $order['payment_method'] : $order->payment_method;
                        $payStatus = is_array($order) ? $order['payment_status'] : $order->payment_status;
                        $rawDate = is_array($order) ? ($order['created_at'] ?? '') : ($order->created_at ?? '');
                        $date = $rawDate instanceof \DateTimeInterface ? $rawDate->format('d/m/Y H:i') : (string) $rawDate;
                        
                        $summaryProd = is_array($order) ? ($order['summary_product'] ?? 'Sản phẩm nội thất') : '';
                        if (!$summaryProd && $order instanceof \App\Models\Order && $order->relationLoaded('items')) {
                            $summaryProd = $order->items->first()->product_name ?? 'Đơn hàng';
                            if ($order->items->count() > 1) {
                                $summaryProd .= ' (+' . ($order->items->count() - 1) . ' món)';
                            }
                        }

                        $refundNote = is_array($order) ? ($order['refund_status'] ?? null) : null;
                        if (!$refundNote && $order instanceof \App\Models\Order && $order->relationLoaded('returnRequests')) {
                            $compRefund = $order->returnRequests->where('status', 'completed')->where('return_type', 'refund')->first();
                            if ($compRefund) {
                                $refundNote = 'Đã hoàn tiền (' . number_format($compRefund->refund_amount, 0, ',', '.') . '₫)';
                            }
                        }
                    @endphp
                    <tr>
                        <td><strong>#{{ $code }}</strong></td>
                        <td>
                            <div class="fw-semibold">{{ $name }}</div>
                            <div class="text-muted small">{{ $phone }}</div>
                        </td>
                        <td>
                            <span class="text-truncate d-inline-block" style="max-width: 220px;" title="{{ $summaryProd }}">
                                {{ $summaryProd ?: 'Sản phẩm nội thất AURA' }}
                            </span>
                        </td>
                        <td>
                            <strong style="color: var(--primary);">{{ number_format($total, 0, ',', '.') }}₫</strong>
                        </td>
                        <td>
                            <span class="badge {{ $payStatus === 'paid' ? 'badge-success' : ($payStatus === 'cancelled' ? 'badge-danger' : 'badge-warning') }}">
                                {{ strtoupper($method) }}: {{ $payStatus === 'paid' ? 'Đã TT' : ($payStatus === 'cancelled' ? 'Đã huỷ' : 'Chờ TT') }}
                            </span>
                        </td>
                        <td>
                            @if($st === 'delivered')
                                <span class="badge badge-success"><i class="fas fa-check-circle me-1"></i> Đã giao hàng</span>
                            @elseif($st === 'shipping')
                                <span class="badge badge-info"><i class="fas fa-truck me-1"></i> Đang vận chuyển</span>
                            @elseif($st === 'preparing' || $st === 'awaiting_shipment')
                                <span class="badge badge-warning"><i class="fas fa-clock me-1"></i> Đang chuẩn bị</span>
                            @elseif($st === 'cancelled')
                                <span class="badge badge-danger"><i class="fas fa-ban me-1"></i> Đã huỷ đơn</span>
                            @else
                                <span class="badge badge-secondary">{{ $st }}</span>
                            @endif
                        </td>
                        <td class="text-muted small">{{ $date }}</td>
                        <td>
                            @if($refundNote)
                                <span class="badge badge-danger text-wrap" style="font-size: 0.75rem;">
                                    {{ $refundNote }}
                                </span>
                            @elseif($st === 'delivered')
                                <span class="badge badge-success" style="font-size: 0.72rem;">Hợp lệ</span>
                            @else
                                <span class="text-muted small">&mdash;</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">
                            Chưa có đơn hàng nào được ghi nhận.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
