<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('order_products', function (Blueprint $table) {
            if (!Schema::hasColumn('order_products', 'size')) {
                $table->string('size')->nullable()->after('quantity');
            }
            if (!Schema::hasColumn('order_products', 'color')) {
                $table->string('color')->nullable()->after('size');
            }
            if (!Schema::hasColumn('order_products', 'subtotal')) {
                $table->decimal('subtotal', 10, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('order_products', 'order_id')) {
                $table->foreignId('order_id')->nullable()->constrained()->after('id');
            }
        });
    }

    public function down()
    {
        Schema::table('order_products', function (Blueprint $table) {
            $table->dropColumn(['size', 'color', 'subtotal', 'order_id']);
        });
    }
};