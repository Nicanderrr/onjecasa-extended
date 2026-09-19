<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\BranchContext;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function index(): View
    {
        $branchId = BranchContext::activeId();
        $summary = [
            'receipt_count' => DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))->count(),
            'total_value' => (float) DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))->sum('p.amount'),
            'today_value' => (float) DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))->whereDate('p.created_at', now()->toDateString())->sum('p.amount'),
            'latest_receipt_at' => DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))->max('p.created_at'),
        ];

        $receipts = DB::table('pos_orders as o')
            ->join('pos_payments as p', 'p.order_id', '=', 'o.id')
            ->leftJoin('users as cashier', 'cashier.id', '=', 'o.cashier_user_id')
            ->when($branchId, fn ($query) => $query->where('o.branch_id', $branchId))
            ->select(
                'o.id',
                'o.code',
                'o.customer_name',
                'o.grand_total',
                'o.status',
                'o.created_at',
                'p.method',
                'p.amount',
                'p.paystack_reference',
                'cashier.name as cashier_name'
            )
            ->orderByDesc('o.id')
            ->paginate(20);

        return view('pos_admin.receipts.index', compact('receipts', 'summary'));
    }

    public function show(int $id): View
    {
        $order = BranchContext::scope(DB::table('pos_orders'))->where('id', $id)->first();
        abort_unless($order, 404);
        $items = DB::table('pos_order_items as i')
            ->join('pos_products as p', 'p.id', '=', 'i.product_id')
            ->select('i.*', 'p.name as product_name')
            ->where('i.order_id', $id)->get();
        $payment = DB::table('pos_payments')->where('order_id', $id)->first();

        return view('pos_admin.receipts.show', compact('order', 'items', 'payment'));
    }

    public function print(int $id): View
    {
        $order = BranchContext::scope(DB::table('pos_orders'))->where('id', $id)->first();
        abort_unless($order, 404);
        $items = DB::table('pos_order_items as i')
            ->join('pos_products as p', 'p.id', '=', 'i.product_id')
            ->select('i.*', 'p.name as product_name')
            ->where('i.order_id', $id)
            ->get();
        $payment = DB::table('pos_payments')->where('order_id', $id)->first();

        return view('pos_admin.receipts.print', compact('order', 'items', 'payment'));
    }
}
