<?php

namespace App\Http\Controllers\Cashier;

use App\Http\Controllers\Controller;
use App\Support\BranchContext;
use App\Support\AuditTrail;
use App\Support\MealExtras;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class SaleController extends Controller
{
    public function create(): View
    {
        $products = DB::table('pos_products')->where('branch_id', BranchContext::activeId())->orderBy('name')->get();
        $mealExtras = MealExtras::options();

        return view('cashier.sales.create', compact('products', 'mealExtras'));
    }

    public function store(Request $request): RedirectResponse
    {
        $base = $request->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'payment_method' => ['required', 'string', 'max:50'],
            'items' => ['required', 'array'],
            'items.*.product_id' => ['nullable', 'integer'],
            'items.*.qty' => ['nullable', 'integer', 'min:1'],
            'items.*.extras' => ['nullable', 'array'],
            'items.*.extra_quantities' => ['nullable', 'array'],
        ]);

        $items = collect($base['items'])
            ->filter(fn ($item) => !empty($item['product_id']) && !empty($item['qty']))
            ->values()
            ->all();

        if (count($items) === 0) {
            throw ValidationException::withMessages(['items' => 'Add at least one product item.']);
        }

        $productIds = collect($items)->pluck('product_id')->unique()->values();

        $orderId = null;

        DB::transaction(function () use ($base, $items, $productIds, &$orderId) {
            $branchId = BranchContext::activeId();
            $products = DB::table('pos_products')
                ->where('branch_id', $branchId)
                ->whereIn('id', $productIds)
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $grandTotal = 0;
            $lineItems = [];

            foreach ($items as $item) {
                $product = $products->get((int)$item['product_id']);

                if (! $product) {
                    throw ValidationException::withMessages(['items' => 'Product not found.']);
                }

                if ((int)$product->stock < (int)$item['qty']) {
                    throw ValidationException::withMessages(['items' => 'Insufficient stock for ' . $product->name]);
                }

                $encodedExtras = MealExtras::encodeSelection(
                    (array) ($item['extras'] ?? []),
                    (array) ($item['extra_quantities'] ?? [])
                );
                $unitPrice = (float) $product->price + MealExtras::total($encodedExtras);
                $lineTotal = $unitPrice * (int)$item['qty'];
                $grandTotal += $lineTotal;

                $lineItems[] = [
                    'product_id' => $product->id,
                    'qty' => (int)$item['qty'],
                    'price' => $unitPrice,
                    'cost_price' => (float) ($product->cost_price ?? 0),
                    'extras' => $encodedExtras,
                    'total' => $lineTotal,
                ];
            }

            $orderId = DB::table('pos_orders')->insertGetId([
                'branch_id' => $branchId,
                'code' => 'ORD-' . now()->format('YmdHis') . '-' . random_int(100, 999),
                'source_uuid' => (string) Str::uuid(),
                'source_system' => (string) config('offline_pos.source_id'),
                'customer_name' => $base['customer_name'],
                'cashier_user_id' => auth()->id(),
                'grand_total' => $grandTotal,
                'status' => 'paid',
                'sync_status' => config('offline_pos.mode') === 'local' ? 'pending' : 'synced',
                'synced_at' => config('offline_pos.mode') === 'local' ? null : now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($lineItems as $lineItem) {
                DB::table('pos_order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $lineItem['product_id'],
                    'qty' => $lineItem['qty'],
                    'price' => $lineItem['price'],
                    'cost_price' => $lineItem['cost_price'],
                    'extras' => $lineItem['extras'],
                    'total' => $lineItem['total'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                DB::table('pos_products')
                    ->where('id', $lineItem['product_id'])
                    ->where('branch_id', $branchId)
                    ->decrement('stock', $lineItem['qty']);
            }

            DB::table('pos_payments')->insert([
                'order_id' => $orderId,
                'method' => $base['payment_method'],
                'amount' => $grandTotal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        AuditTrail::record('sale_created', 'Created paid cashier sale #' . $orderId, [
            'auditable_type' => 'order',
            'auditable_id' => $orderId,
            'properties' => [
                'customer_name' => $base['customer_name'],
                'payment_method' => $base['payment_method'],
                'item_count' => count($items),
            ],
        ]);

        return redirect()->route('cashier.receipts.show', ['id' => $orderId, 'autoprint' => 1])
            ->with('success', 'Payment successful. Receipt generated.');
    }
}
