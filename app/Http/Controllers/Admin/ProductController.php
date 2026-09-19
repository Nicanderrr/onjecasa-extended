<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = DB::table('pos_products')->orderBy('name')->get();

        return view('pos_admin.products.index', compact('products'));
    }
}
