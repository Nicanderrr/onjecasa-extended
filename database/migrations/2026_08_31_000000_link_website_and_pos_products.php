<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_pages', function (Blueprint $table) {
            if (! Schema::hasColumn('product_pages', 'pos_product_id')) {
                $table->unsignedBigInteger('pos_product_id')->nullable()->after('id')->index();
            }
        });

        Schema::table('pos_products', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_products', 'website_product_id')) {
                $table->unsignedBigInteger('website_product_id')->nullable()->after('id')->index();
            }
        });
    }

    public function down(): void
    {
        Schema::table('product_pages', function (Blueprint $table) {
            if (Schema::hasColumn('product_pages', 'pos_product_id')) {
                $table->dropColumn('pos_product_id');
            }
        });

        Schema::table('pos_products', function (Blueprint $table) {
            if (Schema::hasColumn('pos_products', 'website_product_id')) {
                $table->dropColumn('website_product_id');
            }
        });
    }
};
