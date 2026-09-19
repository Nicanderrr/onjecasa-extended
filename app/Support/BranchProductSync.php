<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BranchProductSync
{
    public static function syncProductToBranches(object|array $product, ?string $oldCode = null, bool $syncStock = false): void
    {
        $product = (object) $product;

        if (empty($product->code) || empty($product->branch_id) || ! Schema::hasTable('branches')) {
            return;
        }

        $code = (string) $product->code;
        $oldCode = $oldCode ?: $code;

        if ($oldCode !== $code && Schema::hasTable('branch_product_exclusions')) {
            DB::table('branch_product_exclusions')
                ->where('product_code', $oldCode)
                ->update(['product_code' => $code, 'updated_at' => now()]);
        }

        self::activeBranches()
            ->where('id', '<>', (int) $product->branch_id)
            ->each(function ($branch) use ($product, $oldCode, $code, $syncStock) {
                if (self::isExcluded((int) $branch->id, $code) || self::isExcluded((int) $branch->id, $oldCode)) {
                    return;
                }

                $existing = DB::table('pos_products')
                    ->where('branch_id', $branch->id)
                    ->where('code', $oldCode)
                    ->first();

                if (! $existing && $oldCode !== $code) {
                    $existing = DB::table('pos_products')
                        ->where('branch_id', $branch->id)
                        ->where('code', $code)
                        ->first();
                }

                if ($oldCode !== $code) {
                    $conflict = DB::table('pos_products')
                        ->where('branch_id', $branch->id)
                        ->where('code', $code)
                        ->when($existing, fn ($query) => $query->where('id', '<>', $existing->id))
                        ->exists();

                    if ($conflict) {
                        return;
                    }
                }

                $payload = self::payload($product, (int) $branch->id, $syncStock, $existing);

                if ($existing) {
                    DB::table('pos_products')->where('id', $existing->id)->update($payload);
                    return;
                }

                $payload['created_at'] = now();
                DB::table('pos_products')->insert($payload);
            });
    }

    public static function syncBranchFromCatalog(int $branchId, ?int $sourceBranchId = null): int
    {
        if (! Schema::hasTable('pos_products')) {
            return 0;
        }

        $sourceProducts = self::catalogProducts($sourceBranchId);
        $created = 0;

        foreach ($sourceProducts as $product) {
            if (self::isExcluded($branchId, (string) $product->code)) {
                continue;
            }

            $existing = DB::table('pos_products')
                ->where('branch_id', $branchId)
                ->where('code', $product->code)
                ->first();

            $payload = self::payload($product, $branchId, true, $existing);

            if ($existing) {
                DB::table('pos_products')->where('id', $existing->id)->update($payload);
                continue;
            }

            $payload['created_at'] = now();
            DB::table('pos_products')->insert($payload);
            $created++;
        }

        return $created;
    }

    public static function syncAllBranchesFromCatalog(?int $sourceBranchId = null): int
    {
        return self::activeBranches()
            ->sum(fn ($branch) => self::syncBranchFromCatalog((int) $branch->id, $sourceBranchId));
    }

    public static function markRemoved(int $branchId, string $code, ?int $userId = null): void
    {
        if (! Schema::hasTable('branch_product_exclusions') || $code === '') {
            return;
        }

        DB::table('branch_product_exclusions')->updateOrInsert(
            ['branch_id' => $branchId, 'product_code' => $code],
            ['removed_by' => $userId, 'updated_at' => now(), 'created_at' => now()]
        );
    }

    public static function restore(int $branchId, string $code): void
    {
        if (! Schema::hasTable('branch_product_exclusions') || $code === '') {
            return;
        }

        DB::table('branch_product_exclusions')
            ->where('branch_id', $branchId)
            ->where('product_code', $code)
            ->delete();
    }

    private static function catalogProducts(?int $sourceBranchId = null): Collection
    {
        $query = DB::table('pos_products')
            ->whereNotNull('code')
            ->where('code', '<>', '')
            ->orderBy('id');

        if ($sourceBranchId) {
            $query->where('branch_id', $sourceBranchId);
        }

        return $query->get()->unique('code')->values();
    }

    private static function activeBranches(): Collection
    {
        return DB::table('branches')->where('is_active', true)->orderBy('id')->get();
    }

    private static function isExcluded(int $branchId, string $code): bool
    {
        return Schema::hasTable('branch_product_exclusions')
            && DB::table('branch_product_exclusions')
                ->where('branch_id', $branchId)
                ->where('product_code', $code)
                ->exists();
    }

    private static function payload(object $product, int $branchId, bool $syncStock, ?object $existing = null): array
    {
        $payload = [
            'branch_id' => $branchId,
            'code' => (string) $product->code,
            'name' => (string) $product->name,
            'description' => (string) ($product->description ?? ''),
            'price' => $product->price,
            'cost_price' => $product->cost_price ?? 0,
            'image' => $product->image ?? null,
            'updated_at' => now(),
        ];

        if (Schema::hasColumn('pos_products', 'website_product_id')) {
            $payload['website_product_id'] = $product->website_product_id ?? null;
        }

        if ($syncStock || ! $existing) {
            $payload['stock'] = max(0, (int) ($product->stock ?? 0));
        }

        return $payload;
    }
}
