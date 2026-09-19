<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\BranchContext;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $filters = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date'],
        ]);

        $dateFrom = ! empty($filters['date_from'])
            ? Carbon::parse($filters['date_from'])->startOfDay()
            : now()->startOfDay()->subDays(6);
        $dateTo = ! empty($filters['date_to'])
            ? Carbon::parse($filters['date_to'])->endOfDay()
            : now()->endOfDay();

        if ($dateFrom->gt($dateTo)) {
            [$dateFrom, $dateTo] = [$dateTo->copy()->startOfDay(), $dateFrom->copy()->endOfDay()];
        }

        $branchId = BranchContext::activeId();
        $stats = [
            'product_count' => DB::table('pos_products')->when($branchId, fn ($query) => $query->where('branch_id', $branchId))->count(),
            'order_count' => DB::table('pos_orders')
                ->when($branchId, fn ($query) => $query->where('branch_id', $branchId))
                ->whereBetween('created_at', [$dateFrom, $dateTo])
                ->count(),
            'sales_total' => (float) DB::table('pos_payments as p')
                ->join('pos_orders as o', 'o.id', '=', 'p.order_id')
                ->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))
                ->whereBetween('p.created_at', [$dateFrom, $dateTo])
                ->sum('p.amount'),
            'cashier_count' => DB::table('users')
                ->when($branchId, function ($query) use ($branchId) {
                    $query->join('branch_user', 'branch_user.user_id', '=', 'users.id')
                        ->where('branch_user.branch_id', $branchId);
                })
                ->where('users.role', 'cashier')
                ->where('users.is_active', true)
                ->count(),
        ];

        $orders = DB::table('pos_orders as orders')
            ->leftJoin('users as cashier', 'cashier.id', '=', 'orders.cashier_user_id')
            ->select('orders.*', 'cashier.name as cashier_name')
            ->when($branchId, fn ($query) => $query->where('orders.branch_id', $branchId))
            ->whereBetween('orders.created_at', [$dateFrom, $dateTo])
            ->orderByDesc('orders.id')
            ->limit(10)
            ->get();

        $cashierPerformance = DB::table('users as cashier')
            ->when($branchId, function ($query) use ($branchId) {
                $query->join('branch_user', 'branch_user.user_id', '=', 'cashier.id')
                    ->where('branch_user.branch_id', $branchId);
            })
            ->leftJoin('pos_orders as orders', 'orders.cashier_user_id', '=', 'cashier.id')
            ->where('cashier.role', 'cashier')
            ->where(function ($query) use ($dateFrom, $dateTo) {
                $query->whereBetween('orders.created_at', [$dateFrom, $dateTo])->orWhereNull('orders.id');
            })
            ->when($branchId, function ($query) use ($branchId) {
                $query->where(function ($inner) use ($branchId) {
                    $inner->where('orders.branch_id', $branchId)->orWhereNull('orders.id');
                });
            })
            ->groupBy('cashier.id', 'cashier.name', 'cashier.email', 'cashier.is_active')
            ->selectRaw('cashier.id, cashier.name, cashier.email, cashier.is_active, COUNT(orders.id) as order_count, COALESCE(SUM(orders.grand_total), 0) as sales_total, COALESCE(AVG(orders.grand_total), 0) as average_order, MAX(orders.created_at) as last_sale_at')
            ->orderByDesc('sales_total')
            ->limit(8)
            ->get();

        $chartStart = $dateFrom->copy()->startOfDay();
        $chartEnd = $dateTo->copy()->endOfDay();
        $days = (int) max(0, min(60, $chartStart->diffInDays($chartEnd)));
        $dailySales = DB::table('pos_payments')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_payments.order_id')
            ->when($branchId, fn ($query) => $query->where('pos_orders.branch_id', $branchId))
            ->selectRaw('DATE(pos_payments.created_at) as sale_date, SUM(pos_payments.amount) as total')
            ->whereBetween('pos_payments.created_at', [$chartStart, $chartEnd])
            ->groupByRaw('DATE(pos_payments.created_at)')
            ->pluck('total', 'sale_date');

        $trendDates = collect(range(0, $days))->map(fn (int $day) => $chartStart->copy()->addDays($day));
        $paymentMix = DB::table('pos_payments')
            ->join('pos_orders', 'pos_orders.id', '=', 'pos_payments.order_id')
            ->when($branchId, fn ($query) => $query->where('pos_orders.branch_id', $branchId))
            ->selectRaw('pos_payments.method, SUM(pos_payments.amount) as total')
            ->whereBetween('pos_payments.created_at', [$dateFrom, $dateTo])
            ->groupBy('pos_payments.method')
            ->orderByDesc('total')
            ->get();

        $chartData = [
            'sales_labels' => $trendDates->map(fn ($date) => $date->format('D'))->values(),
            'sales_values' => $trendDates
                ->map(fn ($date) => (float) ($dailySales[$date->toDateString()] ?? 0))
                ->values(),
            'payment_labels' => $paymentMix->pluck('method')->values(),
            'payment_values' => $paymentMix->pluck('total')->map(fn ($total) => (float) $total)->values(),
        ];

        $dateFilters = [
            'date_from' => $dateFrom->toDateString(),
            'date_to' => $dateTo->toDateString(),
        ];

        return view('pos_admin.dashboard.index', compact('stats', 'orders', 'cashierPerformance', 'chartData', 'dateFilters'));
    }
}
