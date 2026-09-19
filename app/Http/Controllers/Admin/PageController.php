<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $page): View
    {
        $allowed = ['dashboard','products','categories','employees','orders','payments','receipts','orders-reports','payments-reports','sales','settings'];
        abort_unless(in_array($page, $allowed, true), 404);

        $data = [
            'page' => $page,
            'products' => DB::table('pos_products')->orderBy('name')->get(),
            'orders' => DB::table('pos_orders')->orderByDesc('id')->limit(20)->get(),
            'payments' => DB::table('pos_payments')->orderByDesc('id')->limit(20)->get(),
            'items' => DB::table('pos_order_items as i')->join('pos_products as p','p.id','=','i.product_id')->select('i.*','p.name as product_name')->orderByDesc('i.id')->limit(30)->get(),
            'stats' => [
                'product_count' => DB::table('pos_products')->count(),
                'order_count' => DB::table('pos_orders')->count(),
                'sales_total' => (float) DB::table('pos_payments')->sum('amount'),
            ],
        ];

        return view('pos_admin.pages.show', $data);
    }
}
