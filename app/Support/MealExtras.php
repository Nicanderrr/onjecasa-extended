<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\MealExtra;
use Illuminate\Support\Facades\Schema;

class MealExtras
{
    public static function options(bool $activeOnly = true): array
    {
        if (Schema::hasTable('meal_extras')) {
            $query = MealExtra::query()->orderBy('sort_order')->orderBy('name');

            if ($activeOnly) {
                $query->where('is_active', true);
            }

            $extras = $query->get()->map(fn (MealExtra $extra) => [
                'name' => $extra->name,
                'price' => (float) $extra->price,
                'icon' => $extra->icon ?: 'bi-basket2',
                'description' => $extra->description,
                'image_url' => $extra->image_url,
                'is_active' => (bool) $extra->is_active,
            ])->all();

            if (! empty($extras)) {
                return $extras;
            }
        }

        return self::defaultOptions();
    }

    public static function defaultOptions(): array
    {
        return [
            ['name' => 'Carrier Bag', 'price' => 1.00, 'icon' => 'bi-bag', 'description' => 'Reusable shopping bag for checkout.', 'image_url' => BrandAssets::logoUrl(), 'is_active' => true],
            ['name' => 'Gift Wrap', 'price' => 5.00, 'icon' => 'bi-gift', 'description' => 'Simple gift wrapping for selected items.', 'image_url' => BrandAssets::logoUrl(), 'is_active' => true],
            ['name' => 'Ice Pack', 'price' => 3.00, 'icon' => 'bi-snow', 'description' => 'Cold pack for chilled products.', 'image_url' => BrandAssets::logoUrl(), 'is_active' => true],
            ['name' => 'Delivery Crate', 'price' => 10.00, 'icon' => 'bi-box-seam', 'description' => 'Reusable crate for bulk orders.', 'image_url' => BrandAssets::logoUrl(), 'is_active' => true],
            ['name' => 'Warranty Card', 'price' => 0.00, 'icon' => 'bi-shield-check', 'description' => 'Warranty card for eligible products.', 'image_url' => BrandAssets::logoUrl(), 'is_active' => true],
            ['name' => 'Batteries', 'price' => 8.00, 'icon' => 'bi-battery-charging', 'description' => 'Add batteries for compatible products.', 'image_url' => BrandAssets::logoUrl(), 'is_active' => true],
            ['name' => 'Cleaning Wipes', 'price' => 6.00, 'icon' => 'bi-stars', 'description' => 'Multipurpose cleaning wipes.', 'image_url' => BrandAssets::logoUrl(), 'is_active' => true],
            ['name' => 'Water 500ml', 'price' => 5.00, 'icon' => 'bi-droplet', 'description' => 'Bottled water 500ml.', 'image_url' => BrandAssets::logoUrl(), 'is_active' => true],
        ];
    }

    public static function encodeSelection(array $selected, array $quantities): ?string
    {
        $allowed = collect(self::options())->keyBy('name');

        $extras = collect($selected)
            ->map(fn ($name) => (string) $name)
            ->filter(fn ($name) => $allowed->has($name))
            ->map(function ($name) use ($allowed, $quantities) {
                $quantity = max(1, (int) ($quantities[$name] ?? 1));
                $option = $allowed->get($name);

                return [
                    'name' => $name,
                    'price' => (float) $option['price'],
                    'quantity' => $quantity,
                ];
            })
            ->values();

        return $extras->isEmpty() ? null : json_encode(['extras' => $extras], JSON_THROW_ON_ERROR);
    }

    public static function decode(?string $stored): array
    {
        if (! $stored) {
            return [];
        }

        $decoded = json_decode($stored, true);

        if (is_array($decoded) && isset($decoded['extras']) && is_array($decoded['extras'])) {
            return collect($decoded['extras'])
                ->map(fn ($extra) => [
                    'name' => (string) ($extra['name'] ?? ''),
                    'price' => (float) ($extra['price'] ?? 0),
                    'quantity' => max(1, (int) ($extra['quantity'] ?? 1)),
                ])
                ->filter(fn ($extra) => $extra['name'] !== '')
                ->values()
                ->all();
        }

        return [['name' => $stored, 'price' => 0, 'quantity' => 1]];
    }

    public static function total(?string $stored): float
    {
        return collect(self::decode($stored))->sum(fn ($extra) => (float) $extra['price'] * (int) $extra['quantity']);
    }

    public static function label(?string $stored): ?string
    {
        $extras = self::decode($stored);

        if (empty($extras)) {
            return null;
        }

        return collect($extras)
            ->map(function ($extra) {
                $price = (float) $extra['price'] * (int) $extra['quantity'];
                $suffix = $price > 0 ? ' +GHC ' . number_format($price, 2) : '';

                return $extra['name'] . ' x' . $extra['quantity'] . $suffix;
            })
            ->implode(', ');
    }

    public static function unitPrice(Cart $item): float
    {
        return ProductOptions::cartBasePrice($item) + self::total($item->color);
    }

    public static function lineTotal(Cart $item): float
    {
        return self::unitPrice($item) * (int) $item->quantity;
    }
}
