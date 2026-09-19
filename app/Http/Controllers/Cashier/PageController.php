<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Support\BranchContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PageController extends Controller
{
    public function show(string $page): View
    {
        $allowed = ['dashboard','products','orders','payments','receipts','orders-reports','payments-reports','sales','settings','new-sale'];
        abort_unless(in_array($page, $allowed, true), 404);
        $branchId = BranchContext::activeId();

        $data = [
            'page' => $page,
            'products' => DB::table('pos_products')->where('branch_id', $branchId)->orderBy('name')->get(),
            'orders' => DB::table('pos_orders')->where('branch_id', $branchId)->orderByDesc('id')->limit(20)->get(),
            'payments' => DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->where('o.branch_id', $branchId)->select('p.*')->orderByDesc('p.id')->limit(20)->get(),
            'items' => DB::table('pos_order_items as i')->join('pos_products as p','p.id','=','i.product_id')->join('pos_orders as o','o.id','=','i.order_id')->where('o.branch_id', $branchId)->select('i.*','p.name as product_name')->orderByDesc('i.id')->limit(30)->get(),
            'stats' => [
                'product_count' => DB::table('pos_products')->where('branch_id', $branchId)->count(),
                'order_count' => DB::table('pos_orders')->where('branch_id', $branchId)->count(),
                'sales_total' => (float) DB::table('pos_payments as p')->join('pos_orders as o', 'o.id', '=', 'p.order_id')->where('o.branch_id', $branchId)->sum('p.amount'),
                'profit_total' => (float) DB::table('pos_order_items as i')
                    ->join('pos_products as p', 'p.id', '=', 'i.product_id')
                    ->join('pos_orders as o', 'o.id', '=', 'i.order_id')
                    ->where('o.branch_id', $branchId)
                    ->selectRaw('COALESCE(SUM((i.price - COALESCE(i.cost_price, p.cost_price, 0)) * i.qty), 0) as total')
                    ->value('total'),
            ],
        ];

        return view('cashier.pages.show', $data);
    }

    public function updateSettings(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash', Rule::unique('users', 'username')->ignore($user->id), Rule::unique('pos_staff', 'username')->ignore(DB::table('pos_staff')->where('user_id', $user->id)->value('id'))],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id), Rule::unique('pos_staff', 'email')->ignore(DB::table('pos_staff')->where('user_id', $user->id)->value('id'))],
            'password' => ['nullable', 'string', 'min:6', 'confirmed'],
            'email_notifications_enabled' => ['nullable', 'boolean'],
        ]);

        $user->name = $data['name'];
        $user->username = strtolower(trim($data['username']));
        $user->email = $data['email'] ?? null;
        $user->email_notifications_enabled = $request->boolean('email_notifications_enabled');
        if (! empty($data['password'])) {
            $user->password = Hash::make($data['password']);
        }
        $user->save();

        DB::table('pos_staff')->where('user_id', $user->id)->update([
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'updated_at' => now(),
        ]);

        return redirect()->route('cashier.pages.show', 'settings')->with('success', 'Settings updated.');
    }
}
