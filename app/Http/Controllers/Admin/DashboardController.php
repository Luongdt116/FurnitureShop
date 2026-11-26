<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\RevenueCalculationService;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected RevenueCalculationService $revenueService
    ) {}

    /**
     * Display Admin KPI Dashboard with 6-month accounting trend, categories, recent orders, and stock alerts.
     */
    public function index(): View
    {
        $categoryBreakdown = $this->revenueService->getCategoryBreakdown();
        $kpi = $this->revenueService->getSummaryMetrics();
        $revenueTrend = $this->revenueService->getMonthlyRevenueTrend(6);
        $recentOrders = $this->revenueService->getRecentOrders(6);
        $lowStockProducts = $this->revenueService->getLowStockProducts(5, 5);

        // Member count
        $customerCount = User::query()->where('role', 'customer')->count();

        return view('admin.dashboard', compact(
            'kpi',
            'revenueTrend',
            'categoryBreakdown',
            'recentOrders',
            'lowStockProducts',
            'customerCount'
        ));
    }
}
