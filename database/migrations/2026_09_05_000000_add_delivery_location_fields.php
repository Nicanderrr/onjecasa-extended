<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            if (! Schema::hasColumn('branches', 'latitude')) {
                $table->decimal('latitude', 10, 7)->nullable()->after('address');
            }
            if (! Schema::hasColumn('branches', 'longitude')) {
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            }
            if (! Schema::hasColumn('branches', 'base_delivery_fee')) {
                $table->decimal('base_delivery_fee', 10, 2)->default(15)->after('longitude');
            }
            if (! Schema::hasColumn('branches', 'delivery_fee_per_km')) {
                $table->decimal('delivery_fee_per_km', 10, 2)->default(3)->after('base_delivery_fee');
            }
            if (! Schema::hasColumn('branches', 'max_delivery_distance_km')) {
                $table->decimal('max_delivery_distance_km', 10, 2)->nullable()->after('delivery_fee_per_km');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'fulfillment_method')) {
                $table->string('fulfillment_method')->default('delivery')->after('total');
            }
            if (! Schema::hasColumn('orders', 'delivery_address')) {
                $table->text('delivery_address')->nullable()->after('fulfillment_method');
            }
            if (! Schema::hasColumn('orders', 'delivery_latitude')) {
                $table->decimal('delivery_latitude', 10, 7)->nullable()->after('delivery_address');
            }
            if (! Schema::hasColumn('orders', 'delivery_longitude')) {
                $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
            }
            if (! Schema::hasColumn('orders', 'delivery_distance_km')) {
                $table->decimal('delivery_distance_km', 10, 2)->default(0)->after('delivery_longitude');
            }
            if (! Schema::hasColumn('orders', 'delivery_fee')) {
                $table->decimal('delivery_fee', 10, 2)->default(0)->after('delivery_distance_km');
            }
            if (! Schema::hasColumn('orders', 'delivery_status')) {
                $table->string('delivery_status')->default('pending')->after('delivery_fee');
            }
        });

        Schema::table('order_products', function (Blueprint $table) {
            if (! Schema::hasColumn('order_products', 'delivery_latitude')) {
                $table->decimal('delivery_latitude', 10, 7)->nullable()->after('fulfillment_method');
            }
            if (! Schema::hasColumn('order_products', 'delivery_longitude')) {
                $table->decimal('delivery_longitude', 10, 7)->nullable()->after('delivery_latitude');
            }
            if (! Schema::hasColumn('order_products', 'delivery_distance_km')) {
                $table->decimal('delivery_distance_km', 10, 2)->default(0)->after('delivery_longitude');
            }
            if (! Schema::hasColumn('order_products', 'delivery_fee')) {
                $table->decimal('delivery_fee', 10, 2)->default(0)->after('delivery_distance_km');
            }
            if (! Schema::hasColumn('order_products', 'delivery_status')) {
                $table->string('delivery_status')->default('pending')->after('delivery_fee');
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_products', function (Blueprint $table) {
            foreach (['delivery_status', 'delivery_fee', 'delivery_distance_km', 'delivery_longitude', 'delivery_latitude'] as $column) {
                if (Schema::hasColumn('order_products', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            foreach (['delivery_status', 'delivery_fee', 'delivery_distance_km', 'delivery_longitude', 'delivery_latitude', 'delivery_address', 'fulfillment_method'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('branches', function (Blueprint $table) {
            foreach (['max_delivery_distance_km', 'delivery_fee_per_km', 'base_delivery_fee', 'longitude', 'latitude'] as $column) {
                if (Schema::hasColumn('branches', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
