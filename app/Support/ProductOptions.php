<?php

namespace App\Support;

use App\Models\Cart;
use App\Models\ProductPage;

class ProductOptions
{
    public static function options(ProductPage $product): array
    {
        $stored = is_string($product->sizes) ? json_decode($product->sizes, true) : $product->sizes;
        $stored = is_array($stored) && count($stored) ? $stored : ['Single Item'];
        $basePrice = (float) $product->price;

        return collect($stored)
            ->map(function ($option) use ($basePrice) {
                if (is_array($option)) {
                    $name = trim((string) ($option['name'] ?? $option['label'] ?? ''));
                    $price = array_key_exists('price', $option) ? (float) $option['price'] : $basePrice;
                } else {
                    $name = trim((string) $option);
                    $price = self::suggestedPrice($name, $basePrice);
                }

                return $name === '' ? null : [
                    'name' => $name,
                    'price' => max(0, round($price, 2)),
                ];
            })
            ->filter()
            ->unique('name')
            ->values()
            ->all();
    }

    public static function names(ProductPage $product): array
    {
        return collect(self::options($product))->pluck('name')->all();
    }

    public static function selectedPrice(ProductPage $product, ?string $selected): float
    {
        $options = collect(self::options($product));
        $selected = trim((string) $selected);

        return (float) ($options->firstWhere('name', $selected)['price'] ?? $options->first()['price'] ?? $product->price ?? 0);
    }

    public static function cartBasePrice(Cart $item): float
    {
        if ($item->price !== null && $item->price !== '') {
            return (float) $item->price;
        }

        return $item->product ? self::selectedPrice($item->product, $item->size) : 0;
    }

    public static function normalizeForStorage(array $selected, array $prices, float $basePrice): array
    {
        return collect($selected)
            ->map(fn ($name) => trim((string) $name))
            ->filter()
            ->unique()
            ->map(fn ($name) => [
                'name' => $name,
                'price' => max(0, round(
                    isset($prices[$name]) && $prices[$name] !== ''
                        ? (float) $prices[$name]
                        : self::suggestedPrice($name, $basePrice),
                    2
                )),
            ])
            ->values()
            ->all();
    }

    public static function suggestedPrice(string $name, float $basePrice): float
    {
        $lower = strtolower($name);

        $multipliers = [
            '24 pack' => 24,
            '12 pack' => 12,
            '10 pack' => 10,
            '8 pack' => 8,
            '6 pack' => 6,
            '4 pack' => 4,
            '2 bags' => 2,
            '2 bottles' => 2,
            'carton' => 24,
            'crate' => 12,
            'box' => 12,
            'bulk' => 10,
            'wholesale' => 12,
            'family' => 5,
            'value' => 3,
            'bundle' => 4,
            'small pack' => 3,
            '5kg' => 5,
            '10kg' => 10,
        ];

        foreach ($multipliers as $needle => $multiplier) {
            if (str_contains($lower, $needle)) {
                return $basePrice * $multiplier;
            }
        }

        return $basePrice;
    }
}
