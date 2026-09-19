<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductImage extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_page_id',
        'path',
        'media_type',
        'is_thumbnail',
        'sort_order',
    ];

    protected $casts = [
        'is_thumbnail' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(ProductPage::class, 'product_page_id');
    }

    public function getUrlAttribute(): string
    {
        $path = trim(str_replace('\\', '/', (string) $this->path));

        if ($path === '') {
            return asset('upload/no_image.jpg');
        }

        if (preg_match('#^https?://#', $path)) {
            return $path;
        }

        $relativePath = ltrim($path, '/');

        if (file_exists(public_path($relativePath))) {
            return asset($relativePath);
        }

        if (file_exists(storage_path('app/public/' . $relativePath))) {
            return route('product.media', ['path' => $relativePath]);
        }

        return asset('upload/no_image.jpg');
    }

    public function getIsVideoAttribute(): bool
    {
        return $this->media_type === 'video';
    }

    public function getIsImageAttribute(): bool
    {
        return $this->media_type !== 'video';
    }
}
