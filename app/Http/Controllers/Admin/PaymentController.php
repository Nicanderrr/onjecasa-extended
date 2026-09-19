<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\BranchContext;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function index(): View
    {
        $branchId = BranchContext::activeId();
        $summary = [
            'payment_count' => DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))->count(),
            'total_collected' => (float) DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))->sum('p.amount'),
            'cash_total' => (float) DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))->whereRaw('LOWER(p.method) = ?', ['cash'])->sum('p.amount'),
            'mobile_total' => (float) DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))->whereRaw('LOWER(p.method) = ?', ['mobile money'])->sum('p.amount'),
            'latest_payment_at' => DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))->max('p.created_at'),
        ];

        $payments = DB::table('pos_payments as p')
            ->join('pos_orders as o', 'o.id', '=', 'p.order_id')
            ->leftJoin('users as cashier', 'cashier.id', '=', 'o.cashier_user_id')
            ->select(
                'p.id',
                'p.order_id',
                'p.method',
                'p.amount',
                'p.paystack_reference',
                'p.created_at',
                'o.code as order_code',
                'o.customer_name',
                'o.status as order_status',
                'o.grand_total as order_total',
                'cashier.name as cashier_name'
            )
            ->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))
            ->orderByDesc('p.id')
            ->paginate(20);

        return view('pos_admin.payments.index', compact('payments', 'summary'));
    }
}
