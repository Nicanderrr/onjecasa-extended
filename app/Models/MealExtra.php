<?php

namespace App\Models;

use App\Support\BrandAssets;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MealExtra extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'icon',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function getImageUrlAttribute(): string
    {
        $image = trim(str_replace('\\', '/', (string) $this->image));

        if ($image === '') {
            return BrandAssets::logoUrl();
        }

        if (preg_match('#^https?://#', $image)) {
            return $image;
        }

        return asset(ltrim($image, '/'));
    }
}
