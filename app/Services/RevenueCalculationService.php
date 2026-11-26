<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class RevenueCalculationService
{
    /** Request-local accounting data cache keyed by date range. */
    private array $ordersCache = [];

    private const PAYMENT_METHODS = [
        'cod' => 'Thanh toán COD khi nhận hàng',
        'vietqr' => 'Chuyển khoản ngân hàng VietQR',
        'vnpay' => 'Cổng thanh toán điện tử VNPAY',
        'online_demo' => 'Cổng thẻ quốc tế (Demo)',
    ];

    /**
     * Calculate accounting values only from delivered, paid orders. Completed
     * refund requests are capped at their order total before being deducted.
     */
    public function getSummaryMetrics(?Carbon $startDate = null, ?Carbon $endDate = null): array
    {
        return $this->summarizeOrders($this->ordersInRange($startDate, $endDate));
    }

    private function summarizeOrders(Collection $orders): array
    {
        $eligibleOrders = $orders->filter(fn (Order $order): bool => $this->isRevenueEligible($order));

        $grossRevenue = 0.0;
        $totalRefunds = 0.0;
        $netRevenue = 0.0;
        $voucherDiscountTotal = 0.0;
        $refundedOrdersCount = 0;
        $exchangeOrdersCount = 0;
        $ordersWithReturns = 0;

        foreach ($eligibleOrders as $order) {
            $amounts = $this->accountingAmounts($order);
            $grossRevenue += $amounts['gross'];
            $totalRefunds += $amounts['refund'];
            $netRevenue += $amounts['net'];
            $voucherDiscountTotal += (float) $order->discount_amount;

            $completedReturns = $order->returnRequests->where('status', 'completed');
            $hasRefund = $completedReturns->contains(fn ($return): bool => $return->return_type === 'refund');
            $hasExchange = $completedReturns->contains(fn ($return): bool => $return->return_type === 'exchange');
            $refundedOrdersCount += $hasRefund ? 1 : 0;
            $exchangeOrdersCount += $hasExchange ? 1 : 0;
            $ordersWithReturns += ($hasRefund || $hasExchange) ? 1 : 0;
        }

        $totalOrdersCount = $orders->count();
        $deliveredCount = $eligibleOrders->count();
        $cancelledCount = $orders->where('status', 'cancelled')->count();

        return [
            'gross_revenue' => $grossRevenue,
            'total_refunds' => $totalRefunds,
            'net_revenue' => $netRevenue,
            'voucher_discount_total' => $voucherDiscountTotal,
            'total_orders_count' => $totalOrdersCount,
            'delivered_orders_count' => $deliveredCount,
            'cancelled_orders_count' => $cancelledCount,
            'refunded_orders_count' => $refundedOrdersCount,
            'exchange_orders_count' => $exchangeOrdersCount,
            'aov' => $deliveredCount > 0 ? round($netRevenue / $deliveredCount) : 0,
            'return_rate' => $totalOrdersCount > 0 ? round(($ordersWithReturns / $totalOrdersCount) * 100, 2) : 0.0,
            'cancellation_rate' => $totalOrdersCount > 0 ? round(($cancelledCount / $totalOrdersCount) * 100, 2) : 0.0,
        ];
    }

    /** Get the recent monthly accounting trend. Empty months report zero. */
    public function getMonthlyRevenueTrend(int $months = 6): array
    {
        $trend = [];
        $now = Carbon::now();
        $monthCount = max(1, $months);
        $firstMonth = $now->copy()->subMonths($monthCount - 1)->startOfMonth();
        $ordersByMonth = $this->ordersInRange($firstMonth, $now->copy()->endOfMonth())
            ->groupBy(fn (Order $order): string => $order->created_at->format('Y-m'));

        for ($i = $monthCount - 1; $i >= 0; $i--) {
            $targetMonth = $now->copy()->subMonths($i);
            $metrics = $this->summarizeOrders($ordersByMonth->get($targetMonth->format('Y-m'), collect()));

            $trend[] = [
                'month_key' => $targetMonth->format('Y-m'),
                'label' => 'T' . $targetMonth->format('m'),
                'full_label' => 'Tháng ' . $targetMonth->format('m/Y'),
                'gross_revenue' => $metrics['gross_revenue'],
                'refunds' => $metrics['total_refunds'],
                'net_revenue' => $metrics['net_revenue'],
                'orders_count' => $metrics['delivered_orders_count'],
            ];
        }

        return $trend;
    }

    /** Category values are net revenue allocated by each eligible order's item-value share. */
    public function getCategoryBreakdown(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $categories = Category::query()->get();
        $results = $categories->map(fn (Category $category) => (object) [
            'id' => $category->id,
            'name' => $category->name,
            'slug' => $category->slug,
            'revenue' => 0.0,
            'quantity' => 0,
            'percentage' => 0.0,
        ])->keyBy('id');

        $eligibleOrders = $this->ordersInRange($startDate, $endDate, ['items.product.category'])
            ->filter(fn (Order $order): bool => $this->isRevenueEligible($order));

        $totalNet = 0.0;
        foreach ($eligibleOrders as $order) {
            $net = $this->accountingAmounts($order)['net'];
            $categoryLines = $order->items
                ->filter(fn ($item) => $item->product?->category && $results->has($item->product->category->id))
                ->groupBy(fn ($item) => $item->product->category->id);
            $lineGross = (float) $categoryLines->flatten(1)->sum('line_total');

            if ($lineGross <= 0 || $net <= 0) {
                continue;
            }

            foreach ($categoryLines as $categoryId => $items) {
                $weight = (float) $items->sum('line_total') / $lineGross;
                $results[$categoryId]->revenue += $net * $weight;
                $results[$categoryId]->quantity += (int) $items->sum('quantity');
            }
            $totalNet += $net;
        }

        foreach ($results as $category) {
            $category->percentage = $totalNet > 0 ? round(($category->revenue / $totalNet) * 100, 1) : 0.0;
        }

        return $results->sortByDesc('revenue')->values();
    }

    /** Net revenue by payment method, using the same eligible orders/refund rule as the summary. */
    public function getPaymentMethodBreakdown(?Carbon $startDate = null, ?Carbon $endDate = null): Collection
    {
        $results = collect(self::PAYMENT_METHODS)->map(fn (string $name, string $method) => (object) [
            'method' => $method,
            'name' => $name,
            'revenue' => 0.0,
            'count' => 0,
            'percentage' => 0.0,
        ])->keyBy('method');

        $eligibleOrders = $this->ordersInRange($startDate, $endDate)
            ->filter(fn (Order $order): bool => $this->isRevenueEligible($order));

        $totalNet = 0.0;
        foreach ($eligibleOrders as $order) {
            $method = $order->payment_method;
            if (! $results->has($method)) {
                $results[$method] = (object) [
                    'method' => $method,
                    'name' => strtoupper(str_replace('_', ' ', $method)),
                    'revenue' => 0.0,
                    'count' => 0,
                    'percentage' => 0.0,
                ];
            }

            $net = $this->accountingAmounts($order)['net'];
            $results[$method]->revenue += $net;
            $results[$method]->count++;
            $totalNet += $net;
        }

        foreach ($results as $method) {
            $method->percentage = $totalNet > 0 ? round(($method->revenue / $totalNet) * 100, 1) : 0.0;
        }

        return $results->sortByDesc('revenue')->values();
    }

    public function getRecentOrders(int $limit = 6): Collection
    {
        return Order::query()
            ->with(['user', 'items.product', 'returnRequests'])
            ->latest('created_at')
            ->take($limit)
            ->get();
    }

    public function getLowStockProducts(int $threshold = 5, int $limit = 5): Collection
    {
        return Product::query()
            ->with('category')
            ->where('stock', '<=', $threshold)
            ->where('is_active', true)
            ->orderBy('stock')
            ->take($limit)
            ->get();
    }

    public function getOrderReconciliationList(
        ?Carbon $startDate = null,
        ?Carbon $endDate = null,
        bool $paginate = true,
    ): Collection|LengthAwarePaginator {
        $query = Order::query()
            ->with(['user', 'coupon', 'returnRequests', 'items', 'payments'])
            ->when($startDate, fn ($query) => $query->where('created_at', '>=', $startDate->copy()->startOfDay()))
            ->when($endDate, fn ($query) => $query->where('created_at', '<=', $endDate->copy()->endOfDay()))
            ->latest('created_at');

        $orders = $paginate ? $query->paginate(15)->withQueryString() : $query->get();
        $rows = $paginate ? $orders->getCollection() : $orders;

        $rows->each(function (Order $order): void {
            $amounts = $this->accountingAmounts($order);
            $order->setAttribute('accounting_gross_revenue', $amounts['gross']);
            $order->setAttribute('accounting_refund', $amounts['refund']);
            $order->setAttribute('accounting_revenue', $amounts['net']);
        });

        return $orders;
    }

    private function ordersInRange(?Carbon $startDate, ?Carbon $endDate, array $extraRelations = []): Collection
    {
        $start = $startDate?->copy()->startOfDay()->toDateTimeString();
        $end = $endDate?->copy()->endOfDay()->toDateTimeString();
        $cacheKey = ($start ?? '*') . '|' . ($end ?? '*');
        $needsItems = in_array('items.product.category', $extraRelations, true);

        if (isset($this->ordersCache[$cacheKey])
            && ($this->ordersCache[$cacheKey]['with_items'] || ! $needsItems)) {
            return $this->ordersCache[$cacheKey]['orders'];
        }

        $query = Order::query()
            ->with(['returnRequests', 'payments'])
            ->when($start, fn ($query) => $query->where('created_at', '>=', $start))
            ->when($end, fn ($query) => $query->where('created_at', '<=', $end));

        if ($needsItems) {
            $query->with('items.product.category');
        }

        $orders = $query->get();
        $this->ordersCache[$cacheKey] = ['orders' => $orders, 'with_items' => $needsItems];

        return $orders;
    }

    private function isRevenueEligible(Order $order): bool
    {
        return $order->status === 'delivered'
            && ($order->payment_status === 'paid'
                || $order->payments->contains(fn ($payment): bool => in_array($payment->status, ['paid', 'completed'], true)));
    }

    /** @return array{gross: float, refund: float, net: float} */
    private function accountingAmounts(Order $order): array
    {
        $gross = max(0.0, (float) $order->total);
        $eligible = $this->isRevenueEligible($order);
        $recordedRefund = (float) $order->returnRequests
            ->where('status', 'completed')
            ->where('return_type', 'refund')
            ->sum('refund_amount');
        $refund = $eligible ? min($gross, max(0.0, $recordedRefund)) : 0.0;

        return [
            'gross' => $gross,
            'refund' => $refund,
            'net' => $eligible ? max(0.0, $gross - $refund) : 0.0,
        ];
    }
}
