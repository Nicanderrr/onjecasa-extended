<?php

namespace Tests\Unit;

use App\Models\ProductPage;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    public function test_picture_url_returns_storage_url_for_stored_image(): void
    {
        $product = new ProductPage(['picture' => 'uploads/example.jpg']);

        $this->assertSame(Storage::disk('public')->url('uploads/example.jpg'), $product->picture_url);
    }

    public function test_picture_url_returns_default_when_no_image_is_present(): void
    {
        $product = new ProductPage();

        $this->assertSame(asset('upload/no_image.jpg'), $product->picture_url);
    }
}
