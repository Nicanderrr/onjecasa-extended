<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Support\BranchContext;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReceiptController extends Controller
{
    public function show(int $id): View
    {
        $order = DB::table('pos_orders')->where('branch_id', BranchContext::activeId())->where('id', $id)->first();
        abort_unless($order, 404);

        $items = DB::table('pos_order_items as i')
            ->join('pos_products as p', 'p.id', '=', 'i.product_id')
            ->select('i.*', 'p.name as product_name')
            ->where('i.order_id', $id)
            ->get();

        $payment = DB::table('pos_payments')->where('order_id', $id)->first();

        return view('cashier.receipts.show', compact('order', 'items', 'payment'));
    }

    public function print(int $id): View
    {
        $order = DB::table('pos_orders')->where('branch_id', BranchContext::activeId())->where('id', $id)->first();
        abort_unless($order, 404);

        $items = DB::table('pos_order_items as i')
            ->join('pos_products as p', 'p.id', '=', 'i.product_id')
            ->select('i.*', 'p.name as product_name')
            ->where('i.order_id', $id)
            ->get();

        $payment = DB::table('pos_payments')->where('order_id', $id)->first();

        return view('cashier.receipts.print', compact('order', 'items', 'payment'));
    }
}
