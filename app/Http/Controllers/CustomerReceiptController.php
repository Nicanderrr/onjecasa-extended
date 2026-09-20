<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerReceiptController extends Controller
{
    public function show(string $token): View
    {
        $order = DB::table('pos_orders')->where('receipt_token', $token)->first();
        abort_unless($order, 404);
        $items = DB::table('pos_order_items as i')->join('pos_products as p', 'p.id', '=', 'i.product_id')->select('i.*', 'p.name as product_name')->where('i.order_id', $order->id)->get();
        $payment = DB::table('pos_payments')->where('order_id', $order->id)->first();
        $branch = DB::table('branches')->where('id', $order->branch_id)->first();
        return view('customer.receipt', compact('order', 'items', 'payment', 'branch'));
    }
}
