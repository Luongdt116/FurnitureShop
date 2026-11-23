@extends('layouts.admin')

@section('title', 'Bảng Điều Khiển & Thống Kê Kinh Doanh')

@section('content')
{{-- 4 KPI Summary Cards --}}
<div class="kpi-grid" id="admin-kpi-grid">
    <div class="kpi-card" id="admin-kpi-revenue">
        <div>
            <div class="kpi-label">Tổng Doanh Thu Đã Hoàn Thành</div>
            <div id="admin-kpi-total-revenue" class="kpi-val">{{ number_format($kpiRevenue ?? 148000000, 0, ',', '.') }}₫</div>
            <div class="kpi-trend trend-up">
                <i class="fas fa-arrow-trend-up"></i> +18.4% so với tháng trước
            </div>
        </div>
        <div class="kpi-icon-wrap"><i class="fas fa-sack-dollar"></i></div>
    </div>

    <div class="kpi-card" id="admin-kpi-orders">
        <div>
            <div class="kpi-label">Tổng Số Đơn Hàng</div>
            <div id="admin-kpi-total-orders" class="kpi-val">{{ $kpiOrdersCount ?? 24 }} đơn</div>
            <div class="kpi-trend trend-up">
                <i class="fas fa-arrow-trend-up"></i> +12% đơn mới
            </div>
        </div>
        <div class="kpi-icon-wrap" style="color: var(--status-info);"><i class="fas fa-dolly"></i></div>
    </div>

    <div class="kpi-card" id="admin-kpi-users">
        <div>
            <div class="kpi-label">Khách Hàng Thành Viên</div>
            <div id="admin-kpi-total-users" class="kpi-val">{{ $kpiUsersCount ?? 124 }} người</div>
            <div class="kpi-trend trend-up">
                <i class="fas fa-user-plus"></i> +32 tài khoản mới
            </div>
        </div>
        <div class="kpi-icon-wrap" style="color: var(--status-success);"><i class="fas fa-users"></i></div>
    </div>

    <div class="kpi-card" id="admin-kpi-returns">
        <div>
            <div class="kpi-label">Tỷ Lệ Đổi Trả / Hoàn Hàng</div>
            <div id="admin-kpi-return-rate" class="kpi-val">{{ $kpiReturnRate ?? '1.8%' }}</div>
            <div class="kpi-trend trend-up" style="color: var(--status-success);">
                <i class="fas fa-shield-check"></i> Thấp hơn mức trần 3%
            </div>
        </div>
        <div class="kpi-icon-wrap" style="color: #8b5cf6;"><i class="fas fa-shield-halved"></i></div>
    </div>
</div>

{{-- Charts Section --}}
<div class="charts-grid" id="admin-charts-grid">
    <div class="chart-card" id="admin-chart-revenue">
        <div class="chart-header">
            <div>
                <h3 class="chart-title">Tăng Trưởng Doanh Thu 6 Tháng Gần Nhất (Triệu VNĐ)</h3>
                <div style="font-size: 0.8rem; color: var(--text-muted);">Doanh thu thực tế đã hoàn thành và đối soát thanh toán</div>
            </div>
            <span class="badge badge-gold"><i class="fas fa-circle-check"></i> Cập nhật thời gian thực</span>
        </div>
        <svg id="admin-revenue-chart-svg" width="100%" height="230" viewBox="0 0 540 230">
            <!-- Grid Lines -->
            <line x1="40" y1="30" x2="520" y2="30" stroke="#eee" stroke-dasharray="4"/>
            <line x1="40" y1="80" x2="520" y2="80" stroke="#eee" stroke-dasharray="4"/>
            <line x1="40" y1="130" x2="520" y2="130" stroke="#eee" stroke-dasharray="4"/>
            <line x1="40" y1="180" x2="520" y2="180" stroke="#eee"/>
            
            <!-- Axis Labels -->
            <text x="30" y="35" font-size="11" fill="#8c827a" text-anchor="end">200M</text>
            <text x="30" y="85" font-size="11" fill="#8c827a" text-anchor="end">150M</text>
            <text x="30" y="135" font-size="11" fill="#8c827a" text-anchor="end">100M</text>
            <text x="30" y="185" font-size="11" fill="#8c827a" text-anchor="end">0</text>
            
            <text x="75" y="205" font-size="11" fill="#8c827a" text-anchor="middle">T06</text>
            <text x="155" y="205" font-size="11" fill="#8c827a" text-anchor="middle">T07</text>
            <text x="235" y="205" font-size="11" fill="#8c827a" text-anchor="middle">T08</text>
            <text x="315" y="205" font-size="11" fill="#8c827a" text-anchor="middle">T09</text>
            <text x="395" y="205" font-size="11" fill="#8c827a" text-anchor="middle">T10</text>
            <text x="475" y="205" font-size="11" fill="#8c827a" text-anchor="middle">T11</text>
            
            <!-- Spline Line -->
            <path d="M 75 145 Q 115 130 155 125 T 235 110 T 315 95 T 395 70 T 475 48" fill="none" stroke="var(--accent)" stroke-width="3"/>
            <circle cx="75" cy="145" r="4" fill="var(--accent)"/>
            <circle cx="155" cy="125" r="4" fill="var(--accent)"/>
            <circle cx="235" cy="110" r="4" fill="var(--accent)"/>
            <circle cx="315" cy="95" r="4" fill="var(--accent)"/>
            <circle cx="395" cy="70" r="4" fill="var(--accent)"/>
            <circle cx="475" cy="48" r="5" fill="#1c1917" stroke="var(--accent)" stroke-width="2"/>
        </svg>
    </div>

    <div class="chart-card" id="admin-chart-categories">
        <div class="chart-header">
            <h3 class="chart-title">Cơ Cấu Danh Mục Doanh Số</h3>
        </div>
        <div style="display: flex; flex-direction: column; gap: 14px; padding-top: 10px;">
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.86rem; margin-bottom: 6px;">
                    <span>Phòng Khách (Sofa, Bàn trà)</span>
                    <strong>48%</strong>
                </div>
                <div style="height: 8px; background: #eee; border-radius: 4px; overflow: hidden;">
                    <div style="width: 48%; height: 100%; background: var(--accent);"></div>
                </div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.86rem; margin-bottom: 6px;">
                    <span>Phòng Ăn & Bếp (Bàn ăn sồi)</span>
                    <strong>26%</strong>
                </div>
                <div style="height: 8px; background: #eee; border-radius: 4px; overflow: hidden;">
                    <div style="width: 26%; height: 100%; background: #3b82f6;"></div>
                </div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.86rem; margin-bottom: 6px;">
                    <span>Phòng Ngủ (Giường King, Tủ áo)</span>
                    <strong>18%</strong>
                </div>
                <div style="height: 8px; background: #eee; border-radius: 4px; overflow: hidden;">
                    <div style="width: 18%; height: 100%; background: #10b981;"></div>
                </div>
            </div>
            <div>
                <div style="display: flex; justify-content: space-between; font-size: 0.86rem; margin-bottom: 6px;">
                    <span>Làm Việc & Decor Đèn</span>
                    <strong>8%</strong>
                </div>
                <div style="height: 8px; background: #eee; border-radius: 4px; overflow: hidden;">
                    <div style="width: 8%; height: 100%; background: #8b5cf6;"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent Orders Table --}}
<div class="admin-card mb-4" id="admin-recent-orders-card">
    <div class="table-toolbar">
        <h3 style="font-size: 1.15rem; font-weight: 700;">Đơn Hàng Gần Đây Cần Xử Lý</h3>
        <a href="{{ route('admin.orders.index') }}" class="btn btn-outline btn-sm">
            Xem tất cả đơn hàng &rarr;
        </a>
    </div>
    <div class="data-table-wrap">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Mã Đơn</th>
                    <th>Khách Hàng</th>
                    <th>Sản Phẩm</th>
                    <th>Tổng Tiền</th>
                    <th>Trạng Thái</th>
                    <th>Ngày Tạo</th>
                    <th>Thao Tác</th>
                </tr>
            </thead>
            <tbody id="admin-recent-orders-tbody">
                <tr>
                    <td><strong>#ORD-9081</strong></td>
                    <td>Nguyễn Thanh Tùng</td>
                    <td>Sofa Bergamo Da Bò Ý (x1)</td>
                    <td><strong>24.500.000₫</strong></td>
                    <td><span class="badge badge-warning">Chờ xử lý</span></td>
                    <td>22/11/2026</td>
                    <td><a href="#" class="btn btn-outline btn-sm py-1 px-2" style="font-size: 0.75rem;">Chi tiết</a></td>
                </tr>
                <tr>
                    <td><strong>#ORD-9080</strong></td>
                    <td>Lê Hoàng Nam</td>
                    <td>Bàn Ăn Sồi Oslo (x1), Ghế ăn (x4)</td>
                    <td><strong>18.200.000₫</strong></td>
                    <td><span class="badge badge-success">Đang giao</span></td>
                    <td>21/11/2026</td>
                    <td><a href="#" class="btn btn-outline btn-sm py-1 px-2" style="font-size: 0.75rem;">Chi tiết</a></td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection
