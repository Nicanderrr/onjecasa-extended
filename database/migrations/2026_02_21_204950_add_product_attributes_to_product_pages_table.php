<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_pages', function (Blueprint $table) {
            // Add JSON columns for sizes, colors, and features
            $table->json('sizes')->nullable()->after('price');
            $table->json('colors')->nullable()->after('sizes');
            $table->json('features')->nullable()->after('colors');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_pages', function (Blueprint $table) {
            $table->dropColumn(['sizes', 'colors', 'features']);
        });
    }
};