<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        foreach (['orders', 'order_products'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                if (! Schema::hasColumn($tableName, 'assigned_staff_user_id')) {
                    $table->foreignId('assigned_staff_user_id')->nullable()->after('delivery_status')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn($tableName, 'assigned_staff_name')) {
                    $table->string('assigned_staff_name')->nullable()->after('assigned_staff_user_id');
                }
                if (! Schema::hasColumn($tableName, 'confirmed_at')) {
                    $table->timestamp('confirmed_at')->nullable()->after('assigned_staff_name');
                }
                if (! Schema::hasColumn($tableName, 'preparing_at')) {
                    $table->timestamp('preparing_at')->nullable()->after('confirmed_at');
                }
                if (! Schema::hasColumn($tableName, 'out_for_delivery_at')) {
                    $table->timestamp('out_for_delivery_at')->nullable()->after('preparing_at');
                }
                if (! Schema::hasColumn($tableName, 'delivered_at')) {
                    $table->timestamp('delivered_at')->nullable()->after('out_for_delivery_at');
                }
                if (! Schema::hasColumn($tableName, 'cancelled_at')) {
                    $table->timestamp('cancelled_at')->nullable()->after('delivered_at');
                }
                if (! Schema::hasColumn($tableName, 'status_updated_by')) {
                    $table->foreignId('status_updated_by')->nullable()->after('cancelled_at')->constrained('users')->nullOnDelete();
                }
                if (! Schema::hasColumn($tableName, 'status_updated_by_name')) {
                    $table->string('status_updated_by_name')->nullable()->after('status_updated_by');
                }
            });
        }
    }

    public function down(): void
    {
        foreach (['order_products', 'orders'] as $tableName) {
            Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                foreach (['status_updated_by_name', 'cancelled_at', 'delivered_at', 'out_for_delivery_at', 'preparing_at', 'confirmed_at', 'assigned_staff_name'] as $column) {
                    if (Schema::hasColumn($tableName, $column)) {
                        $table->dropColumn($column);
                    }
                }

                if (Schema::hasColumn($tableName, 'status_updated_by')) {
                    $table->dropConstrainedForeignId('status_updated_by');
                }
                if (Schema::hasColumn($tableName, 'assigned_staff_user_id')) {
                    $table->dropConstrainedForeignId('assigned_staff_user_id');
                }
            });
        }
    }
};
