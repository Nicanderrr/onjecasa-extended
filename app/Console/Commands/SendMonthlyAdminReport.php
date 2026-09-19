<?php

namespace App\Console\Commands;

use App\Mail\MonthlyAdminReportMail;
use App\Models\OrderProduct;
use App\Models\ProductPage;
use App\Models\SuperAdminAuditLog;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendMonthlyAdminReport extends Command
{
    protected $signature = 'reports:send-monthly-admin
        {--month= : Report month in YYYY-MM format. Defaults to the previous month.}
        {--to=* : Extra recipient email address. Can be used more than once.}
        {--dry-run : Build the report without sending email.}';

    protected $description = 'Send the monthly business report to admin and superadmin users.';

    public function handle(): int
    {
        [$start, $end] = $this->reportWindow();
        $report = $this->buildReport($start, $end);
        $recipients = $this->recipients();

        foreach ($this->option('to') as $email) {
            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $recipients->push($email);
            }
        }

        $recipients = $recipients->filter()->unique()->values();

        if ($this->option('dry-run')) {
            $this->line('Monthly admin report built for '.$report['period_label'].'.');
            $this->line('Recipients: '.($recipients->isEmpty() ? 'none' : $recipients->implode(', ')));
            $this->line('Total revenue: GHS '.number_format($report['totals']['total_revenue'], 2));
            $this->line('Orders: '.number_format($report['totals']['total_orders']));

            return self::SUCCESS;
        }

        if ($recipients->isEmpty()) {
            $this->warn('No admin recipients found.');

            return self::FAILURE;
        }

        Mail::to($recipients->all())->send(new MonthlyAdminReportMail($report));

        $this->info('Monthly admin report sent to '.$recipients->implode(', ').'.');

        return self::SUCCESS;
    }

    private function reportWindow(): array
    {
        $month = $this->option('month');

        if ($month) {
            $start = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        } else {
            $start = now()->subMonthNoOverflow()->startOfMonth();
        }

        return [$start->copy()->startOfDay(), $start->copy()->endOfMonth()->endOfDay()];
    }

    private function recipients(): Collection
    {
        return User::query()
            ->where(function ($query) {
                $query->whereIn('is_admin', [1, 2])
                    ->orWhereIn('role', ['admin', 'superadmin']);
            })
            ->where(function ($query) {
                $query->whereNull('is_active')->orWhere('is_active', true);
            })
            ->pluck('email');
    }

    private function buildReport(Carbon $start, Carbon $end): array
    {
        $posPayments = DB::table('pos_payments as payments')
            ->join('pos_orders as orders', 'orders.id', '=', 'payments.order_id')
            ->whereBetween('payments.created_at', [$start, $end]);

        $posOrders = DB::table('pos_orders')
            ->whereBetween('created_at', [$start, $end]);

        $onlineOrderLines = OrderProduct::query()
            ->whereBetween('created_at', [$start, $end]);
        $onlineOrders = DB::table('orders')
            ->whereBetween('created_at', [$start, $end]);

        $posRevenue = (float) (clone $posPayments)->sum('payments.amount');
        $onlineRevenue = (float) (clone $onlineOrders)->sum('total');
        $posOrderCount = (int) (clone $posOrders)->count();
        $onlineOrderCount = (int) (clone $onlineOrders)->count();
        $totalOrders = $posOrderCount + $onlineOrderCount;
        $totalRevenue = $posRevenue + $onlineRevenue;

        return [
            'period_label' => $start->format('F Y'),
            'date_from' => $start->toFormattedDateString(),
            'date_to' => $end->toFormattedDateString(),
            'generated_at' => now()->toDayDateTimeString(),
            'totals' => [
                'pos_revenue' => $posRevenue,
                'online_revenue' => $onlineRevenue,
                'total_revenue' => $totalRevenue,
                'pos_orders' => $posOrderCount,
                'online_orders' => $onlineOrderCount,
                'total_orders' => $totalOrders,
                'average_order_value' => $totalOrders > 0 ? $totalRevenue / $totalOrders : 0,
                'online_units' => (int) (clone $onlineOrderLines)->sum('quantity'),
                'pos_units' => (int) DB::table('pos_order_items')
                    ->join('pos_orders', 'pos_orders.id', '=', 'pos_order_items.order_id')
                    ->whereBetween('pos_order_items.created_at', [$start, $end])
                    ->sum('pos_order_items.qty'),
            ],
            'top_pos_products' => $this->topPosProducts($start, $end),
            'top_online_products' => $this->topOnlineProducts($start, $end),
            'branch_performance' => $this->branchPerformance($start, $end),
            'payment_methods' => $this->paymentMethods($start, $end),
            'fulfillment_methods' => $this->fulfillmentMethods($start, $end),
            'order_statuses' => $this->orderStatuses($start, $end),
            'inventory' => [
                'pos_low_stock' => DB::table('pos_products')->where('stock', '<=', 10)->orderBy('stock')->limit(8)->get(['name', 'stock']),
                'online_low_stock' => ProductPage::query()->where('stock', '<=', 10)->orderBy('stock')->limit(8)->get(['name', 'stock']),
            ],
            'activity' => [
                'superadmin_actions' => SuperAdminAuditLog::whereBetween('created_at', [$start, $end])->count(),
                'pos_actions' => DB::table('pos_audit_trails')->whereBetween('created_at', [$start, $end])->count(),
            ],
        ];
    }

    private function topPosProducts(Carbon $start, Carbon $end): Collection
    {
        return DB::table('pos_order_items as items')
            ->join('pos_orders as orders', 'orders.id', '=', 'items.order_id')
            ->leftJoin('pos_products as products', 'products.id', '=', 'items.product_id')
            ->whereBetween('items.created_at', [$start, $end])
            ->groupBy('items.product_id', 'products.name')
            ->selectRaw('COALESCE(products.name, CONCAT("Product #", items.product_id)) as name, SUM(items.qty) as units, SUM(items.total) as revenue')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();
    }

    private function topOnlineProducts(Carbon $start, Carbon $end): Collection
    {
        return OrderProduct::query()
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('product_id', 'product_name')
            ->selectRaw('COALESCE(NULLIF(product_name, ""), CONCAT("Product #", product_id)) as name, SUM(quantity) as units, SUM(total) as revenue')
            ->orderByDesc('revenue')
            ->limit(8)
            ->get();
    }

    private function branchPerformance(Carbon $start, Carbon $end): Collection
    {
        $pos = DB::table('pos_orders')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('branch_id, COUNT(*) as orders, SUM(grand_total) as revenue')
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        $online = DB::table('orders')
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw('branch_id, COUNT(*) as orders, SUM(total) as revenue')
            ->groupBy('branch_id')
            ->get()
            ->keyBy('branch_id');

        return DB::table('branches')->orderBy('name')->get(['id', 'name'])->map(function ($branch) use ($pos, $online) {
            $posRow = $pos->get($branch->id);
            $onlineRow = $online->get($branch->id);

            return (object) [
                'name' => $branch->name,
                'pos_orders' => (int) ($posRow->orders ?? 0),
                'online_orders' => (int) ($onlineRow->orders ?? 0),
                'pos_revenue' => (float) ($posRow->revenue ?? 0),
                'online_revenue' => (float) ($onlineRow->revenue ?? 0),
                'total_revenue' => (float) ($posRow->revenue ?? 0) + (float) ($onlineRow->revenue ?? 0),
            ];
        })->sortByDesc('total_revenue')->values();
    }

    private function paymentMethods(Carbon $start, Carbon $end): Collection
    {
        return DB::table('pos_payments')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('method')
            ->selectRaw('method, COUNT(*) as payments, SUM(amount) as revenue')
            ->orderByDesc('revenue')
            ->get();
    }

    private function fulfillmentMethods(Carbon $start, Carbon $end): Collection
    {
        return DB::table('order_products')
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('fulfillment_method')
            ->selectRaw('COALESCE(fulfillment_method, "not specified") as method, COUNT(DISTINCT order_id) as orders, SUM(total) as revenue')
            ->orderByDesc('revenue')
            ->get();
    }

    private function orderStatuses(Carbon $start, Carbon $end): array
    {
        return [
            'pos' => DB::table('pos_orders')
                ->whereBetween('created_at', [$start, $end])
                ->groupBy('status')
                ->selectRaw('status, COUNT(*) as total')
                ->orderByDesc('total')
                ->get(),
            'online' => DB::table('order_products')
                ->whereBetween('created_at', [$start, $end])
                ->selectRaw('COALESCE(delivery_status, status, "pending") as status, COUNT(DISTINCT order_id) as total')
                ->groupByRaw('COALESCE(delivery_status, status, "pending")')
                ->orderByDesc('total')
                ->get(),
        ];
    }
}
