@extends('layouts.admin')

@section('title', 'Báo Cáo Doanh Thu Kế Toán Thuần Túy')

@push('styles')
<style>
    /* Print Styling for Accounting Financial Statement */
    @page {
        size: A4 landscape;
        margin: 8mm 6mm;
    }

    @media print {
        *,
        *::before,
        *::after {
            box-shadow: none !important;
            text-shadow: none !important;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
        }

        html, body {
            background: #ffffff !important;
            font-size: 8.5pt !important;
            color: #111111 !important;
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
        }

        /* Hide UI & Navigation Elements */
        .admin-sidebar, 
        .admin-topbar, 
        .admin-sidebar-backdrop,
        .no-print, 
        .btn, 
        .alert, 
        .badge-subtle,
        #admin-role-switch-banner {
            display: none !important;
        }

        .admin-main {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }
        .admin-content {
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
            overflow: visible !important;
        }

        .print-only {
            display: block !important;
        }

        /* 1. Header Print */
        .print-only.mb-4 {
            margin-bottom: 12px !important;
            padding-bottom: 8px !important;
        }
        .print-only h2 {
            font-size: 13pt !important;
            margin: 0 !important;
            font-weight: 800 !important;
            color: #000000 !important;
        }
        .print-only h3 {
            font-size: 11pt !important;
            margin: 4px 0 !important;
            font-weight: 700 !important;
            color: #333333 !important;
        }
        .print-only p {
            font-size: 8pt !important;
            margin: 2px 0 0 0 !important;
            color: #555555 !important;
        }

        /* 2. KPI Cards - Compact Row for Print */
        #admin-report-kpis {
            display: flex !important;
            flex-wrap: nowrap !important;
            gap: 6px !important;
            margin-bottom: 12px !important;
            width: 100% !important;
        }
        #admin-report-kpis > div {
            flex: 1 1 0 !important;
            width: 16.666% !important;
            max-width: 16.666% !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        #admin-report-kpis .admin-card {
            padding: 6px 8px !important;
            border: 1px solid #888 !important;
            background: #fafafa !important;
            min-height: 68px !important;
            border-radius: 3px !important;
        }
        #admin-report-kpis .kpi-icon-wrap {
            display: none !important;
        }
        #admin-report-kpis .font-heading {
            font-size: 9.5pt !important;
            font-weight: 800 !important;
            margin: 2px 0 !important;
        }
        #admin-report-kpis .small,
        #admin-report-kpis .text-uppercase {
            font-size: 6.5pt !important;
            line-height: 1.2 !important;
        }

        /* 3. Category & Payment Breakdown Rows */
        .row.g-4.mb-4 {
            display: flex !important;
            flex-wrap: nowrap !important;
            gap: 10px !important;
            margin-bottom: 12px !important;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        .row.g-4.mb-4 > .col-lg-6 {
            flex: 0 0 50% !important;
            width: 50% !important;
            max-width: 50% !important;
            padding: 0 !important;
        }

        /* 4. Complete Reset for Tables in Print */
        .admin-card {
            border: 1px solid #777 !important;
            border-radius: 3px !important;
            box-shadow: none !important;
            margin-bottom: 12px !important;
            overflow: visible !important;
        }
        .table-toolbar {
            padding: 6px 10px !important;
            border-bottom: 1px solid #888 !important;
            background: #f0f0f0 !important;
        }
        .table-toolbar h3 {
            font-size: 8.5pt !important;
            font-weight: 700 !important;
        }
        .table-toolbar span {
            font-size: 7pt !important;
        }

        .data-table-wrap {
            overflow: visible !important;
            width: 100% !important;
            max-width: 100% !important;
            display: block !important;
            border: none !important;
        }

        .data-table {
            width: 100% !important;
            max-width: 100% !important;
            min-width: 0 !important;
            table-layout: fixed !important;
            border-collapse: collapse !important;
            font-size: 7.5pt !important;
            color: #000000 !important;
            background: #ffffff !important;
        }

        .data-table thead {
            display: table-header-group !important;
        }
        .data-table tbody tr {
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }

        .data-table th,
        .data-table td {
            border: 1px solid #888888 !important;
            padding: 4px 5px !important;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: break-word !important;
            min-width: 0 !important;
            line-height: 1.25 !important;
            vertical-align: middle !important;
        }
        .data-table th {
            background: #eaeaea !important;
            color: #000000 !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            font-size: 7.2pt !important;
        }

        /* 5. Specific Column Width Distribution for 10-column Reconciliation Table */
        #admin-reconciliation-table {
            min-width: 0 !important;
            width: 100% !important;
            table-layout: fixed !important;
        }
        #admin-reconciliation-table th:nth-child(1),
        #admin-reconciliation-table td:nth-child(1) { width: 9% !important; font-weight: 700; } /* Mã đơn */
        #admin-reconciliation-table th:nth-child(2),
        #admin-reconciliation-table td:nth-child(2) { width: 7.5% !important; } /* Ngày HT */
        #admin-reconciliation-table th:nth-child(3),
        #admin-reconciliation-table td:nth-child(3) { width: 14.5% !important; } /* Khách hàng */
        #admin-reconciliation-table th:nth-child(4),
        #admin-reconciliation-table td:nth-child(4) { width: 7% !important; text-align: center; } /* Kênh TT */
        #admin-reconciliation-table th:nth-child(5),
        #admin-reconciliation-table td:nth-child(5) { width: 10% !important; text-align: right; } /* Tổng Gộp */
        #admin-reconciliation-table th:nth-child(6),
        #admin-reconciliation-table td:nth-child(6) { width: 8.5% !important; text-align: right; } /* Voucher */
        #admin-reconciliation-table th:nth-child(7),
        #admin-reconciliation-table td:nth-child(7) { width: 9.5% !important; text-align: right; } /* Hoàn trả */
        #admin-reconciliation-table th:nth-child(8),
        #admin-reconciliation-table td:nth-child(8) { width: 12% !important; text-align: right; font-weight: 800; } /* Doanh Thu Thuần */
        #admin-reconciliation-table th:nth-child(9),
        #admin-reconciliation-table td:nth-child(9) { width: 8% !important; text-align: center; } /* Trạng Thái */
        #admin-reconciliation-table th:nth-child(10),
        #admin-reconciliation-table td:nth-child(10) { width: 14% !important; } /* Ghi Chú Kế Toán */

        /* Badges inside tables for print */
        .data-table .badge {
            border: 1px solid #777 !important;
            background: transparent !important;
            color: #000000 !important;
            font-size: 6.5pt !important;
            padding: 1px 4px !important;
            font-weight: 600 !important;
        }

        /* 6. Signatures Block */
        .print-only.mt-5 {
            margin-top: 18px !important;
            padding-top: 10px !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
        }
        .print-only.mt-5 strong {
            font-size: 8.5pt !important;
        }
        .print-only.mt-5 p {
            font-size: 7.5pt !important;
            margin-top: 45px !important;
        }
    }
    .print-only {
        display: none;
    }
    .period-pill {
        padding: 6px 14px;
        border-radius: var(--radius-full);
        font-size: 0.82rem;
        font-weight: 600;
        text-decoration: none;
        color: var(--text-secondary);
        background: #ffffff;
        border: 1px solid var(--border-color);
        transition: all var(--transition-fast);
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }
    .period-pill:hover {
        border-color: var(--accent);
        color: var(--accent);
    }
    .period-pill.active {
        background: var(--primary);
        color: #ffffff;
        border-color: var(--primary);
        box-shadow: var(--shadow-xs);
    }
</style>
@endpush

@section('content')
<div class="container-fluid p-0" id="admin-report-container">
    {{-- Header for Print Only --}}
    <div class="print-only mb-4 pb-3 border-bottom text-center">
        <h2 style="font-size: 1.6rem; font-weight: 800; text-transform: uppercase;">AURA LIVING &bull; NGHỆ THUẬT KHÔNG GIAN SỐNG</h2>
        <h3 style="font-size: 1.3rem; margin-top: 6px;">BÁO CÁO DOANH THU KẾ TOÁN VÀ ĐỐI SOÁT TÀI CHÍNH</h3>
        <p style="font-size: 0.9rem; color: #555;">
            Kỳ kế toán: <strong>{{ strtoupper($period) }}</strong> &bull; Thời gian in: {{ date('d/m/Y H:i:s') }}
        </p>
    </div>

    {{-- Screen Header --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 no-print" id="admin-report-screen-header">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <span class="badge badge-gold" style="font-size: 0.72rem;"><i class="fas fa-calculator"></i> PURE ACCOUNTING</span>
                <h2 class="h4 font-heading mb-0 text-primary">Báo Cáo Doanh Thu Kế Toán Thuần Túy</h2>
            </div>
            <span class="text-muted small">
                Công thức: <strong>Doanh Thu Thuần = (Đơn Hoàn Thành & Đã Thanh Toán) &minus; (Tiền Hoàn Trả Đổi Trả Đã Xử Lý)</strong>. Đơn huỷ = 0₫. Đổi hàng = giữ nguyên.
            </span>
        </div>

        {{-- Export & Print Buttons --}}
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.reports.revenue.export', request()->all()) }}" class="btn btn-outline btn-sm shadow-xs" id="admin-export-excel-btn">
                <i class="fas fa-file-excel text-success me-1"></i> Xuất Excel (CSV)
            </a>
            <button type="button" onclick="window.print()" class="btn btn-accent btn-sm shadow-xs" id="admin-print-report-btn">
                <i class="fas fa-print me-1"></i> In Báo Cáo
            </button>
        </div>
    </div>

    {{-- Filter Toolbar --}}
    <div class="admin-card mb-4 no-print" id="admin-report-filter-card">
        <div class="p-3 bg-white">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                {{-- Preset Period Pills --}}
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <span class="text-muted small fw-semibold me-1"><i class="fas fa-calendar-days text-accent me-1"></i> Kỳ báo cáo:</span>
                    <a href="{{ route('admin.reports.revenue', ['period' => 'today']) }}" class="period-pill {{ $period === 'today' ? 'active' : '' }}">Hôm nay</a>
                    <a href="{{ route('admin.reports.revenue', ['period' => '7days']) }}" class="period-pill {{ $period === '7days' ? 'active' : '' }}">7 ngày qua</a>
                    <a href="{{ route('admin.reports.revenue', ['period' => 'this_month']) }}" class="period-pill {{ $period === 'this_month' ? 'active' : '' }}">Tháng này</a>
                    <a href="{{ route('admin.reports.revenue', ['period' => 'last_month']) }}" class="period-pill {{ $period === 'last_month' ? 'active' : '' }}">Tháng trước</a>
                    <a href="{{ route('admin.reports.revenue', ['period' => 'quarter']) }}" class="period-pill {{ $period === 'quarter' ? 'active' : '' }}">Quý này</a>
                    <a href="{{ route('admin.reports.revenue', ['period' => 'year']) }}" class="period-pill {{ $period === 'year' ? 'active' : '' }}">Năm nay</a>
                </div>

                {{-- Custom Date Form --}}
                <form action="{{ route('admin.reports.revenue') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2 m-0 p-0" id="admin-report-custom-filter-form">
                    <input type="hidden" name="period" value="custom">
                    <div class="input-group input-group-sm" style="width: auto; min-width: 140px;">
                        <span class="input-group-text bg-light text-muted" style="font-size: 0.78rem;">Từ</span>
                        <input type="date" name="date_from" value="{{ $dateFromFormatted }}" class="form-control form-control-sm" required style="font-size: 0.8rem;">
                    </div>
                    <div class="input-group input-group-sm" style="width: auto; min-width: 140px;">
                        <span class="input-group-text bg-light text-muted" style="font-size: 0.78rem;">Đến</span>
                        <input type="date" name="date_to" value="{{ $dateToFormatted }}" class="form-control form-control-sm" required style="font-size: 0.8rem;">
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm px-3" style="font-size: 0.82rem; white-space: nowrap;">
                        <i class="fas fa-filter me-1"></i> Lọc
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- 6 Financial Accounting KPI Cards --}}
    <div class="row g-3 mb-4" id="admin-report-kpis">
        {{-- Net Revenue --}}
        <div class="col-xl-4 col-md-6">
            <div class="admin-card p-3 p-lg-4 h-100 position-relative overflow-hidden" style="border-left: 4px solid var(--accent);">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <span class="text-uppercase fw-bold text-muted small" style="letter-spacing: 0.05em; font-size: 0.78rem;">DOANH THU THUẦN KẾ TOÁN</span>
                    <div class="kpi-icon-wrap" style="width: 38px; height: 38px; font-size: 1.1rem; background: var(--accent-subtle); color: var(--accent); flex-shrink: 0;">
                        <i class="fas fa-scale-balanced"></i>
                    </div>
                </div>
                <div class="font-heading fw-bold mb-1" style="color: var(--accent); font-size: clamp(1.3rem, 1.6vw, 1.7rem);">
                    {{ number_format($kpi['net_revenue'], 0, ',', '.') }}₫
                </div>
                <div class="small text-muted" style="font-size: 0.78rem;">
                    = Gộp ({{ number_format($kpi['gross_revenue'], 0, ',', '.') }}₫) &minus; Hoàn trả ({{ number_format($kpi['total_refunds'], 0, ',', '.') }}₫)
                </div>
            </div>
        </div>

        {{-- Gross Revenue --}}
        <div class="col-xl-4 col-md-6">
            <div class="admin-card p-3 p-lg-4 h-100 position-relative overflow-hidden" style="border-left: 4px solid var(--status-info);">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <span class="text-uppercase fw-bold text-muted small" style="letter-spacing: 0.05em; font-size: 0.78rem;">DOANH THU GỘP (DELIVERED & PAID)</span>
                    <div class="kpi-icon-wrap" style="width: 38px; height: 38px; font-size: 1.1rem; background: rgba(59, 130, 246, 0.1); color: #3b82f6; flex-shrink: 0;">
                        <i class="fas fa-sack-dollar"></i>
                    </div>
                </div>
                <div class="font-heading fw-bold mb-1 text-primary" style="font-size: clamp(1.3rem, 1.6vw, 1.7rem);">
                    {{ number_format($kpi['gross_revenue'], 0, ',', '.') }}₫
                </div>
                <div class="small text-muted" style="font-size: 0.78rem;">
                    Tính trên <strong>{{ $kpi['delivered_orders_count'] }}</strong> đơn giao thành công & đối soát đủ
                </div>
            </div>
        </div>

        {{-- Total Refunds Deduction --}}
        <div class="col-xl-4 col-md-6">
            <div class="admin-card p-3 p-lg-4 h-100 position-relative overflow-hidden" style="border-left: 4px solid var(--status-danger);">
                <div class="d-flex align-items-start justify-content-between mb-2">
                    <span class="text-uppercase fw-bold text-muted small" style="letter-spacing: 0.05em; font-size: 0.78rem;">TỔNG TIỀN HOÀN TRẢ ĐÃ TRỪ</span>
                    <div class="kpi-icon-wrap" style="width: 38px; height: 38px; font-size: 1.1rem; background: rgba(239, 68, 68, 0.1); color: #ef4444; flex-shrink: 0;">
                        <i class="fas fa-arrow-rotate-left"></i>
                    </div>
                </div>
                <div class="font-heading fw-bold mb-1 text-danger" style="font-size: clamp(1.3rem, 1.6vw, 1.7rem);">
                    -{{ number_format($kpi['total_refunds'], 0, ',', '.') }}₫
                </div>
                <div class="small text-muted" style="font-size: 0.78rem;">
                    Từ <strong>{{ $kpi['refunded_orders_count'] }}</strong> yêu cầu hoàn tiền (Refund Completed)
                </div>
            </div>
        </div>

        {{-- Voucher Cost --}}
        <div class="col-xl-4 col-md-6">
            <div class="admin-card p-3 h-100" style="border-left: 3px solid #8b5cf6;">
                <div class="text-muted small fw-semibold" style="font-size: 0.78rem;">Chi phí giảm giá Voucher</div>
                <div class="font-heading fw-bold my-1" style="color: #8b5cf6; font-size: clamp(1.15rem, 1.4vw, 1.4rem);">
                    {{ number_format($kpi['voucher_discount_total'], 0, ',', '.') }}₫
                </div>
                <span class="text-muted small" style="font-size: 0.76rem;">Khuyến mãi kích cầu đã tài trợ</span>
            </div>
        </div>

        {{-- Average Order Value (AOV) --}}
        <div class="col-xl-4 col-md-6">
            <div class="admin-card p-3 h-100" style="border-left: 3px solid #10b981;">
                <div class="text-muted small fw-semibold" style="font-size: 0.78rem;">Giá Trị Đơn Trung Bình (AOV)</div>
                <div class="font-heading fw-bold my-1 text-success" style="font-size: clamp(1.15rem, 1.4vw, 1.4rem);">
                    {{ number_format($kpi['aov'], 0, ',', '.') }}₫
                </div>
                <span class="text-muted small" style="font-size: 0.76rem;">Doanh thu thuần / Đơn hoàn tất</span>
            </div>
        </div>

        {{-- Return & Cancellation Rate --}}
        <div class="col-xl-4 col-md-6">
            <div class="admin-card p-3 h-100" style="border-left: 3px solid #f59e0b;">
                <div class="text-muted small fw-semibold" style="font-size: 0.78rem;">Tỷ lệ Hoàn trả / Huỷ đơn</div>
                <div class="font-heading fw-bold my-1 text-warning" style="font-size: clamp(1.15rem, 1.4vw, 1.4rem);">
                    {{ $kpi['return_rate'] }}% <span style="font-size: 0.8rem; color: var(--text-muted); font-weight: normal;">(Huỷ: {{ $kpi['cancellation_rate'] }}%)</span>
                </div>
                <span class="text-muted small" style="font-size: 0.76rem;">{{ $kpi['cancelled_orders_count'] }} đơn huỷ (ghi nhận 0₫) &bull; {{ $kpi['exchange_orders_count'] }} đổi hàng</span>
            </div>
        </div>
    </div>

    {{-- Category & Payment Method Analysis --}}
    <div class="row g-4 mb-4">
        {{-- Category Share Table --}}
        <div class="col-lg-6">
            <div class="admin-card h-100" id="admin-report-category-card">
                <div class="table-toolbar">
                    <h3 class="h6 mb-0 font-heading"><i class="fas fa-layer-group text-accent me-2"></i> Doanh Thu Theo Danh Mục Sản Phẩm</h3>
                    <span class="text-muted small">Đơn hoàn tất</span>
                </div>
                <div class="data-table-wrap">
                    <table class="data-table admin-table-mini">
                        <thead>
                            <tr>
                                <th style="min-width: 150px;">Danh Mục</th>
                                <th class="text-center" style="min-width: 90px;">Số Lượng Bán</th>
                                <th class="text-end" style="min-width: 120px;">Doanh Số (₫)</th>
                                <th class="text-end" style="min-width: 80px;">Tỷ Trọng</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($categoryBreakdown as $cat)
                                <tr>
                                    <td><strong>{{ $cat->name }}</strong></td>
                                    <td class="text-center">{{ $cat->quantity }} món</td>
                                    <td class="text-end fw-semibold text-primary">
                                        {{ number_format($cat->revenue, 0, ',', '.') }}₫
                                    </td>
                                    <td class="text-end">
                                        <span class="badge badge-gold">{{ $cat->percentage }}%</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Payment Method Share Table --}}
        <div class="col-lg-6">
            <div class="admin-card h-100" id="admin-report-payment-card">
                <div class="table-toolbar">
                    <h3 class="h6 mb-0 font-heading"><i class="fas fa-credit-card text-accent me-2"></i> Doanh Thu Theo Phương Thức Thanh Toán</h3>
                    <span class="text-muted small">Đã đối soát</span>
                </div>
                <div class="data-table-wrap">
                    <table class="data-table admin-table-mini">
                        <thead>
                            <tr>
                                <th style="min-width: 150px;">Kênh Thanh Toán</th>
                                <th class="text-center" style="min-width: 90px;">Số Đơn</th>
                                <th class="text-end" style="min-width: 120px;">Doanh Số (₫)</th>
                                <th class="text-end" style="min-width: 80px;">Tỷ Trọng</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($paymentMethodBreakdown as $pay)
                                <tr>
                                    <td>
                                        <span class="fw-semibold">{{ $pay->name }}</span>
                                        <span class="badge badge-secondary ms-1" style="font-size: 0.65rem;">{{ strtoupper($pay->method) }}</span>
                                    </td>
                                    <td class="text-center">{{ $pay->count }} đơn</td>
                                    <td class="text-end fw-semibold text-primary">
                                        {{ number_format($pay->revenue, 0, ',', '.') }}₫
                                    </td>
                                    <td class="text-end">
                                        <span class="badge badge-info">{{ $pay->percentage }}%</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Order Reconciliation Detailed Audit Table --}}
    <div class="admin-card mb-4" id="admin-report-reconciliation-card">
        <div class="table-toolbar">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <h3 class="h6 mb-0 font-heading">
                        <i class="fas fa-list-check text-accent me-2"></i> Bảng Đối Soát Doanh Thu Chi Tiết Từng Đơn Hàng
                    </h3>
                    <span class="badge badge-subtle d-inline-block d-xxl-none text-muted" style="font-size: 0.72rem; background: var(--bg-subtle);">
                        <i class="fas fa-arrows-left-right text-accent me-1"></i> Cuộn ngang để xem đủ 10 cột
                    </span>
                </div>
                <span class="text-muted small">
                    Kiểm tra chi tiết từng đơn hàng, tiền giảm giá voucher, tiền hoàn trả và số tiền ghi nhận vào sổ sách kế toán.
                </span>
            </div>
            <span class="badge badge-gold px-3 py-2 flex-shrink-0">
                Tổng cộng: {{ is_countable($reconciliationList) ? count($reconciliationList) : $reconciliationList->total() }} đơn
            </span>
        </div>

        <div class="data-table-wrap">
            <table class="data-table" id="admin-reconciliation-table">
                <thead>
                    <tr>
                        <th style="min-width: 110px;">Mã Đơn</th>
                        <th style="min-width: 100px;">Ngày HT</th>
                        <th style="min-width: 160px;">Khách Hàng</th>
                        <th style="min-width: 100px;">Kênh TT</th>
                        <th style="min-width: 120px;" class="text-end">Tổng Gộp (₫)</th>
                        <th style="min-width: 100px;" class="text-end">Voucher (₫)</th>
                        <th style="min-width: 110px;" class="text-end">Hoàn Trả (₫)</th>
                        <th style="min-width: 140px;" class="text-end">Doanh Thu Ghi Nhận (₫)</th>
                        <th style="min-width: 120px;">Trạng Thái</th>
                        <th style="min-width: 240px;">Ghi Chú Kế Toán</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reconciliationList as $row)
                        @php
                            $code = $row->order_code;
                            $rawDate = $row->completed_at ?? $row->created_at;
                            $date = $rawDate instanceof \DateTimeInterface ? $rawDate->format('d/m/Y') : (string) $rawDate;
                            $name = $row->recipient_name;
                            $phone = $row->phone;
                            $method = strtoupper($row->payment_method);
                            $gross = (float) $row->accounting_gross_revenue + (float) $row->discount_amount;
                            $discount = (float) $row->discount_amount;
                            $refund = (float) $row->accounting_refund;
                            $accountingRev = (float) $row->accounting_revenue;
                            $status = $row->status;
                            $note = $accountingRev > 0
                                ? ($refund > 0 ? 'Trừ hoàn tiền: -' . number_format($refund, 0, ',', '.') . '₫' : 'Đã đối soát đủ')
                                : 'Chưa hoàn thành hoặc chưa thanh toán';
                        @endphp
                        <tr>
                            <td><strong>#{{ $code }}</strong></td>
                            <td class="text-muted small">{{ $date }}</td>
                            <td>
                                <div class="fw-semibold">{{ $name }}</div>
                                <div class="text-muted small" style="font-size: 0.75rem;">{{ $phone }}</div>
                            </td>
                            <td><span class="badge badge-secondary" style="font-size: 0.7rem;">{{ $method }}</span></td>
                            <td class="text-end">{{ number_format($gross, 0, ',', '.') }}₫</td>
                            <td class="text-end text-muted">
                                {{ $discount > 0 ? '-' . number_format($discount, 0, ',', '.') . '₫' : '0₫' }}
                            </td>
                            <td class="text-end text-danger fw-semibold">
                                {{ $refund > 0 ? '-' . number_format($refund, 0, ',', '.') . '₫' : '0₫' }}
                            </td>
                            <td class="text-end">
                                <strong style="color: {{ $accountingRev > 0 ? 'var(--primary)' : 'var(--text-muted)' }}; font-size: 0.95rem;">
                                    {{ number_format($accountingRev, 0, ',', '.') }}₫
                                </strong>
                            </td>
                            <td>
                                @if($status === 'delivered')
                                    <span class="badge badge-success"><i class="fas fa-check-circle me-1"></i> Hoàn thành</span>
                                @elseif($status === 'cancelled')
                                    <span class="badge badge-danger"><i class="fas fa-ban me-1"></i> Đã huỷ (0₫)</span>
                                @else
                                    <span class="badge badge-warning">{{ $status }}</span>
                                @endif
                            </td>
                            <td style="white-space: normal; min-width: 240px; max-width: 320px;">
                                <span class="small text-muted" style="font-size: 0.78rem;">{{ $note }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-4 text-muted">
                                Không có đơn hàng nào trong kỳ kế toán đã chọn.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($reconciliationList instanceof \Illuminate\Pagination\LengthAwarePaginator && $reconciliationList->hasPages())
            <div class="p-3 border-top d-flex justify-content-center no-print">
                {{ $reconciliationList->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>

    {{-- Signatures block for printing --}}
    <div class="print-only mt-5 pt-4">
        <div class="row text-center">
            <div class="col-4">
                <strong>NGƯỜI LẬP BIỂU</strong>
                <p class="text-muted small" style="margin-top: 50px;">(Ký và ghi rõ họ tên)</p>
            </div>
            <div class="col-4">
                <strong>KẾ TOÁN TRƯỞNG</strong>
                <p class="text-muted small" style="margin-top: 50px;">(Ký và ghi rõ họ tên)</p>
            </div>
            <div class="col-4">
                <strong>GIÁM ĐỐC ĐIỀU HÀNH</strong>
                <p class="text-muted small" style="margin-top: 50px;">(Ký, đóng dấu)</p>
            </div>
        </div>
    </div>
</div>
@endsection
