<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductPage extends Model
{
    use HasFactory;

    protected $guarded = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'sizes' => 'array',
        'colors' => 'array',
        'features' => 'array',
        'stock' => 'integer',
    ];

    /**
     * Get all cart items that include this product.
     */
    public function carts()
    {
        return $this->hasMany(Cart::class, 'product_id');
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class, 'product_page_id')->orderBy('sort_order')->orderBy('id');
    }

    public function thumbnailImage()
    {
        return $this->hasOne(ProductImage::class, 'product_page_id')->where('is_thumbnail', true);
    }

    public function getThumbnailMediaAttribute(): ?ProductImage
    {
        return $this->thumbnailImage()->first() ?: $this->images()->first();
    }

    public function getThumbnailMediaUrlAttribute(): string
    {
        return $this->thumbnail_media?->url ?: $this->picture_url;
    }

    public function getThumbnailMediaTypeAttribute(): string
    {
        return $this->thumbnail_media?->media_type ?: 'image';
    }

    public function getThumbnailIsVideoAttribute(): bool
    {
        return $this->thumbnail_media_type === 'video';
    }

    public function getPictureUrlAttribute(): string
    {
        $picture = trim(str_replace('\\', '/', (string) $this->picture));

        if ($picture === '') {
            return asset('upload/no_image.jpg');
        }

        if (preg_match('#^https?://#', $picture)) {
            return $picture;
        }

        $relativePath = ltrim($picture, '/');

        if (preg_match('/\.(mp4|mov|webm|m4v)$/i', $relativePath)) {
            return $this->images()->where('media_type', 'image')->orderBy('sort_order')->orderBy('id')->first()?->url
                ?: asset('upload/no_image.jpg');
        }

        if (file_exists(public_path($relativePath))) {
            return asset($relativePath);
        }

        if (str_starts_with($relativePath, 'storage/')) {
            if (file_exists(public_path($relativePath))) {
                return asset($relativePath);
            }

            $relativePath = substr($relativePath, strlen('storage/'));
        }

        if (file_exists(storage_path('app/public/' . $relativePath))) {
            return route('product.media', ['path' => $relativePath]);
        }

        if (str_starts_with($relativePath, 'uploads/')) {
            return asset($relativePath);
        }

        return asset('upload/no_image.jpg');
    }

    /**
     * Get available sizes as array
     */
    public function getSizesArrayAttribute()
    {
        if (is_string($this->sizes)) {
            return json_decode($this->sizes, true) ?? [];
        }
        return $this->sizes ?? [];
    }

    /**
     * Get available colors as array
     */
    public function getColorsArrayAttribute()
    {
        if (is_string($this->colors)) {
            return json_decode($this->colors, true) ?? [];
        }
        return $this->colors ?? [];
    }

    /**
     * Get features as array
     */
    public function getFeaturesArrayAttribute()
    {
        if (is_string($this->features)) {
            return json_decode($this->features, true) ?? [];
        }
        return $this->features ?? [];
    }
}
