<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RevenueCalculationService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function __construct(
        protected RevenueCalculationService $revenueService
    ) {}

    /**
     * Display Revenue Accounting Report with filters, breakdown tables, and audit logs.
     */
    public function revenue(Request $request): View
    {
        [$startDate, $endDate, $period] = $this->resolveDateRange($request);

        $categoryBreakdown = $this->revenueService->getCategoryBreakdown($startDate, $endDate);
        $kpi = $this->revenueService->getSummaryMetrics($startDate, $endDate);
        $paymentMethodBreakdown = $this->revenueService->getPaymentMethodBreakdown($startDate, $endDate);
        $monthlyTrend = $this->revenueService->getMonthlyRevenueTrend(6);
        $reconciliationList = $this->revenueService->getOrderReconciliationList($startDate, $endDate);

        $dateFromFormatted = $startDate ? $startDate->format('Y-m-d') : '';
        $dateToFormatted = $endDate ? $endDate->format('Y-m-d') : '';

        return view('admin.reports.revenue', compact(
            'kpi',
            'categoryBreakdown',
            'paymentMethodBreakdown',
            'monthlyTrend',
            'reconciliationList',
            'period',
            'dateFromFormatted',
            'dateToFormatted'
        ));
    }

    /**
     * Export pure accounting revenue report to Excel-compatible CSV.
     */
    public function exportRevenueCsv(Request $request): StreamedResponse
    {
        [$startDate, $endDate, $period] = $this->resolveDateRange($request);
        $kpi = $this->revenueService->getSummaryMetrics($startDate, $endDate);
        $reconciliationList = $this->revenueService->getOrderReconciliationList($startDate, $endDate, paginate: false);

        $fileName = 'Bao_Cao_Doanh_Thu_AURA_' . ($period ?? 'all') . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($kpi, $reconciliationList, $startDate, $endDate, $period) {
            $handle = fopen('php://output', 'w');
            
            // UTF-8 BOM for Microsoft Excel compatibility with Vietnamese characters
            fputs($handle, "\xEF\xBB\xBF");

            // Header summary
            fputcsv($handle, ['BÁO CÁO DOANH THU KẾ TOÁN THUẦN TÚY - AURA LIVING']);
            fputcsv($handle, ['Thời gian xuất báo cáo:', date('d/m/Y H:i:s')]);
            fputcsv($handle, ['Kỳ kế toán:', $period]);
            fputcsv($handle, ['Khoảng thời gian:', ($startDate ? $startDate->format('d/m/Y') : 'Toàn bộ') . ' - ' . ($endDate ? $endDate->format('d/m/Y') : 'Hiện tại')]);
            fputcsv($handle, []);

            // Financial KPIs
            fputcsv($handle, ['CHỈ TIÊU KẾ TOÁN', 'GIÁ TRỊ (VNĐ)']);
            fputcsv($handle, ['Doanh thu gộp (Gross Revenue - Delivered & Paid):', number_format($kpi['gross_revenue'], 0, ',', '.') . '₫']);
            fputcsv($handle, ['Tổng tiền hoàn trả đổi trả (Refund Deductions):', '-' . number_format($kpi['total_refunds'], 0, ',', '.') . '₫']);
            fputcsv($handle, ['DOANH THU THUẦN KẾ TOÁN (Net Accounting Revenue):', number_format($kpi['net_revenue'], 0, ',', '.') . '₫']);
            fputcsv($handle, ['Chi phí giảm giá khuyến mãi (Vouchers):', number_format($kpi['voucher_discount_total'], 0, ',', '.') . '₫']);
            fputcsv($handle, ['Giá trị đơn hàng trung bình (AOV):', number_format($kpi['aov'], 0, ',', '.') . '₫']);
            fputcsv($handle, ['Tỷ lệ hoàn trả (Return Rate):', $kpi['return_rate'] . '%']);
            fputcsv($handle, ['Số đơn hoàn thành:', $kpi['delivered_orders_count']]);
            fputcsv($handle, []);

            // Detail table
            fputcsv($handle, [
                'STT',
                'Mã Đơn Hàng',
                'Ngày Hoàn Thành',
                'Khách Hàng',
                'Số Điện Thoại',
                'Phương Thức Thanh Toán',
                'Doanh Thu Gộp (₫)',
                'Giảm Giá Voucher (₫)',
                'Tiền Hoàn Trả (₫)',
                'Doanh Thu Ghi Nhận (₫)',
                'Trạng Thái Đơn',
                'Ghi Chú Kế Toán',
            ]);

            $index = 1;
            foreach ($reconciliationList as $row) {
                $orderDate = $row->completed_at ?? $row->created_at;
                $formattedDate = $orderDate instanceof \DateTimeInterface ? $orderDate->format('d/m/Y H:i') : (string) $orderDate;
                $refund = (float) $row->accounting_refund;
                $recognizedRevenue = (float) $row->accounting_revenue;

                fputcsv($handle, [
                    $index++,
                    $row->order_code,
                    $formattedDate,
                    $row->recipient_name,
                    $row->phone,
                    strtoupper($row->payment_method),
                    number_format((float) $row->total + (float) $row->discount_amount, 0, ',', '.'),
                    number_format((float) ($row->discount_amount ?? 0), 0, ',', '.'),
                    number_format($refund, 0, ',', '.'),
                    number_format($recognizedRevenue, 0, ',', '.'),
                    $row->status,
                    $recognizedRevenue > 0 ? ($refund > 0 ? "Trừ hoàn tiền {$refund}" : 'Đã đối soát ghi nhận') : 'Chưa ghi nhận (chưa hoàn tất hoặc đã huỷ)',
                ]);
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Resolve date range from request filter.
     */
    private function resolveDateRange(Request $request): array
    {
        $period = $request->query('period', 'this_month');
        $startDate = null;
        $endDate = null;

        switch ($period) {
            case 'today':
                $startDate = Carbon::today();
                $endDate = Carbon::today()->endOfDay();
                break;
            case '7days':
                $startDate = Carbon::now()->subDays(6)->startOfDay();
                $endDate = Carbon::now()->endOfDay();
                break;
            case 'last_month':
                $startDate = Carbon::now()->subMonth()->startOfMonth();
                $endDate = Carbon::now()->subMonth()->endOfMonth();
                break;
            case 'quarter':
                $startDate = Carbon::now()->startOfQuarter();
                $endDate = Carbon::now()->endOfQuarter();
                break;
            case 'year':
                $startDate = Carbon::now()->startOfYear();
                $endDate = Carbon::now()->endOfYear();
                break;
            case 'custom':
                if ($request->filled('date_from')) {
                    $startDate = Carbon::parse($request->query('date_from'))->startOfDay();
                }
                if ($request->filled('date_to')) {
                    $endDate = Carbon::parse($request->query('date_to'))->endOfDay();
                }
                break;
            case 'this_month':
            default:
                $period = 'this_month';
                $startDate = Carbon::now()->startOfMonth();
                $endDate = Carbon::now()->endOfMonth();
                break;
        }

        return [$startDate, $endDate, $period];
    }
}
