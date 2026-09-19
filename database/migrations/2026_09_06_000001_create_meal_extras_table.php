<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('meal_extras', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2)->default(0);
            $table->string('image')->nullable();
            $table->string('icon')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        $now = now();
        DB::table('meal_extras')->insert($this->defaultSupermarketAddOns($now));
    }

    public function down(): void
    {
        Schema::dropIfExists('meal_extras');
    }

    private function defaultSupermarketAddOns($now): array
    {
        return [
            ['name' => 'Carrier Bag', 'description' => 'Reusable shopping bag for checkout.', 'price' => 1.00, 'icon' => 'bi-bag', 'is_active' => true, 'sort_order' => 10, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Gift Wrap', 'description' => 'Simple gift wrapping for selected items.', 'price' => 5.00, 'icon' => 'bi-gift', 'is_active' => true, 'sort_order' => 20, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Ice Pack', 'description' => 'Cold pack for chilled products.', 'price' => 3.00, 'icon' => 'bi-snow', 'is_active' => true, 'sort_order' => 30, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Delivery Crate', 'description' => 'Reusable crate for bulk orders.', 'price' => 10.00, 'icon' => 'bi-box-seam', 'is_active' => true, 'sort_order' => 40, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Warranty Card', 'description' => 'Warranty card for eligible products.', 'price' => 0.00, 'icon' => 'bi-shield-check', 'is_active' => true, 'sort_order' => 50, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Batteries', 'description' => 'Add batteries for compatible products.', 'price' => 8.00, 'icon' => 'bi-battery-charging', 'is_active' => true, 'sort_order' => 60, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Cleaning Wipes', 'description' => 'Multipurpose cleaning wipes.', 'price' => 6.00, 'icon' => 'bi-stars', 'is_active' => true, 'sort_order' => 70, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Water 500ml', 'description' => 'Bottled water 500ml.', 'price' => 5.00, 'icon' => 'bi-droplet', 'is_active' => true, 'sort_order' => 80, 'created_at' => $now, 'updated_at' => $now],
        ];
    }
};

