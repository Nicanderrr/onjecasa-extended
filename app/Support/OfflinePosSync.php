<?php

namespace App\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OfflinePosSync
{
    public static function salePayload(object $order): array
    {
        $branch = $order->branch_id ? DB::table('branches')->where('id', $order->branch_id)->first() : null;
        $cashier = DB::table('users')->where('id', $order->cashier_user_id)->first();
        $payment = DB::table('pos_payments')->where('order_id', $order->id)->orderByDesc('id')->first();
        $items = DB::table('pos_order_items as items')
            ->join('pos_products as products', 'products.id', '=', 'items.product_id')
            ->where('items.order_id', $order->id)
            ->orderBy('items.id')
            ->get([
                'products.code as product_code',
                'products.name as product_name',
                'items.qty',
                'items.price',
                'items.extras',
                'items.total',
                'items.created_at',
            ])
            ->map(fn ($item) => [
                'product_code' => (string) $item->product_code,
                'product_name' => (string) $item->product_name,
                'qty' => (int) $item->qty,
                'price' => (float) $item->price,
                'extras' => $item->extras,
                'total' => (float) $item->total,
                'created_at' => self::dateString($item->created_at),
            ])
            ->all();

        return [
            'source_uuid' => (string) ($order->source_uuid ?: Str::uuid()),
            'source_system' => (string) config('offline_pos.source_id'),
            'local_order_id' => (int) $order->id,
            'code' => (string) $order->code,
            'branch_code' => $branch->code ?? null,
            'customer_name' => (string) $order->customer_name,
            'cashier_email' => $cashier->email ?? null,
            'cashier_username' => $cashier->username ?? null,
            'grand_total' => (float) $order->grand_total,
            'status' => (string) $order->status,
            'created_at' => self::dateString($order->created_at),
            'updated_at' => self::dateString($order->updated_at),
            'payment' => [
                'method' => (string) ($payment->method ?? 'Cash'),
                'amount' => (float) ($payment->amount ?? $order->grand_total),
                'paystack_reference' => $payment->paystack_reference ?? null,
                'created_at' => self::dateString($payment->created_at ?? $order->created_at),
            ],
            'items' => $items,
        ];
    }

    public static function importSale(array $payload): array
    {
        foreach (['source_uuid', 'code', 'customer_name', 'grand_total', 'items'] as $key) {
            if (! array_key_exists($key, $payload)) {
                throw ValidationException::withMessages([$key => 'Missing required sync field.']);
            }
        }

        if (DB::table('pos_orders')->where('source_uuid', $payload['source_uuid'])->exists()) {
            return ['status' => 'duplicate', 'source_uuid' => $payload['source_uuid']];
        }

        $branchId = self::resolveBranchId($payload['branch_code'] ?? null);
        $cashierId = self::resolveCashierId($payload['cashier_email'] ?? null, $payload['cashier_username'] ?? null);
        $createdAt = self::parseDate($payload['created_at'] ?? null);
        $updatedAt = self::parseDate($payload['updated_at'] ?? null) ?: $createdAt;

        DB::transaction(function () use ($payload, $branchId, $cashierId, $createdAt, $updatedAt) {
            $productCodes = collect($payload['items'])
                ->pluck('product_code')
                ->map(fn ($code) => trim((string) $code))
                ->filter()
                ->unique()
                ->values();

            $products = DB::table('pos_products')
                ->whereIn('code', $productCodes)
                ->get()
                ->keyBy('code');

            $missing = $productCodes->reject(fn ($code) => $products->has($code))->values();
            if ($missing->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'items' => 'Missing hosted POS product code(s): ' . $missing->implode(', '),
                ]);
            }

            $remoteCode = self::uniqueOrderCode((string) $payload['code']);
            $orderId = DB::table('pos_orders')->insertGetId([
                'branch_id' => $branchId,
                'code' => $remoteCode,
                'source_uuid' => $payload['source_uuid'],
                'source_system' => $payload['source_system'] ?? null,
                'customer_name' => $payload['customer_name'],
                'cashier_user_id' => $cashierId,
                'grand_total' => (float) $payload['grand_total'],
                'status' => $payload['status'] ?? 'paid',
                'sync_status' => 'synced',
                'synced_at' => now(),
                'created_at' => $createdAt,
                'updated_at' => $updatedAt,
            ]);

            foreach ($payload['items'] as $item) {
                $product = $products->get((string) $item['product_code']);

                DB::table('pos_order_items')->insert([
                    'order_id' => $orderId,
                    'product_id' => $product->id,
                    'qty' => (int) $item['qty'],
                    'price' => (float) $item['price'],
                    'extras' => $item['extras'] ?? null,
                    'total' => (float) $item['total'],
                    'created_at' => self::parseDate($item['created_at'] ?? null) ?: $createdAt,
                    'updated_at' => $updatedAt,
                ]);

                DB::table('pos_products')
                    ->where('id', $product->id)
                    ->decrement('stock', (int) $item['qty']);
            }

            $payment = $payload['payment'] ?? [];
            DB::table('pos_payments')->insert([
                'order_id' => $orderId,
                'method' => $payment['method'] ?? 'Cash',
                'amount' => (float) ($payment['amount'] ?? $payload['grand_total']),
                'paystack_reference' => $payment['paystack_reference'] ?? null,
                'created_at' => self::parseDate($payment['created_at'] ?? null) ?: $createdAt,
                'updated_at' => $updatedAt,
            ]);
        });

        return ['status' => 'imported', 'source_uuid' => $payload['source_uuid']];
    }

    private static function resolveBranchId(?string $branchCode): ?int
    {
        if ($branchCode) {
            $branchId = DB::table('branches')->where('code', $branchCode)->value('id');
            if ($branchId) {
                return (int) $branchId;
            }
        }

        return BranchContext::defaultBranchId();
    }

    private static function resolveCashierId(?string $email, ?string $username = null): int
    {
        $query = DB::table('users');

        if ($email) {
            $cashierId = (clone $query)->where('email', $email)->value('id');
            if ($cashierId) {
                return (int) $cashierId;
            }
        }

        if ($username) {
            $cashierId = (clone $query)->where('username', $username)->value('id');
            if ($cashierId) {
                return (int) $cashierId;
            }
        }

        $fallbackId = DB::table('users')
            ->whereIn('role', ['cashier', 'admin', 'superadmin'])
            ->orWhereIn('is_admin', [1, 2])
            ->orderBy('id')
            ->value('id');

        if (! $fallbackId) {
            throw ValidationException::withMessages(['cashier_email' => 'No hosted POS user exists for this synced sale.']);
        }

        return (int) $fallbackId;
    }

    private static function uniqueOrderCode(string $code): string
    {
        if (! DB::table('pos_orders')->where('code', $code)->exists()) {
            return $code;
        }

        return $code . '-SYNC-' . Str::upper(Str::random(5));
    }

    private static function parseDate(?string $date): Carbon
    {
        return $date ? Carbon::parse($date) : now();
    }

    private static function dateString(mixed $date): string
    {
        return $date ? Carbon::parse($date)->toDateTimeString() : now()->toDateTimeString();
    }
}
