<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeSlide extends Model
{
    use HasFactory;

    protected $fillable = [
        'main_header',
        'small_header',
        'picture',
        'video_path',
    ];

    public function getImageUrlAttribute(): string
    {
        if (empty($this->picture)) {
            return asset('upload/no_image.jpg');
        }

        if (filter_var($this->picture, FILTER_VALIDATE_URL)) {
            return $this->picture;
        }

        if (str_starts_with($this->picture, 'uploads/') || str_starts_with($this->picture, 'contacts/')) {
            return asset('storage/' . $this->picture);
        }

        return asset($this->picture);
    }

    public function getVideoUrlAttribute(): ?string
    {
        if (empty($this->video_path)) {
            return null;
        }

        if (filter_var($this->video_path, FILTER_VALIDATE_URL)) {
            return $this->video_path;
        }

        $path = ltrim(str_replace('\\', '/', (string) $this->video_path), '/');

        if (file_exists(public_path($path))) {
            return asset($path);
        }

        if (file_exists(storage_path('app/public/' . $path))) {
            return asset('storage/' . $path);
        }

        return null;
    }
}
