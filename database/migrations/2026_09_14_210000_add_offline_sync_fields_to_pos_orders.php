<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_orders', 'source_uuid')) {
                $table->uuid('source_uuid')->nullable()->after('code')->unique();
            }

            if (! Schema::hasColumn('pos_orders', 'source_system')) {
                $table->string('source_system')->nullable()->after('source_uuid')->index();
            }

            if (! Schema::hasColumn('pos_orders', 'sync_status')) {
                $table->string('sync_status')->default('pending')->after('status')->index();
            }

            if (! Schema::hasColumn('pos_orders', 'synced_at')) {
                $table->timestamp('synced_at')->nullable()->after('sync_status');
            }

            if (! Schema::hasColumn('pos_orders', 'last_sync_attempt_at')) {
                $table->timestamp('last_sync_attempt_at')->nullable()->after('synced_at');
            }

            if (! Schema::hasColumn('pos_orders', 'sync_error')) {
                $table->text('sync_error')->nullable()->after('last_sync_attempt_at');
            }
        });

        DB::table('pos_orders')
            ->whereNull('source_uuid')
            ->orderBy('id')
            ->select('id')
            ->chunkById(100, function ($orders) {
                foreach ($orders as $order) {
                    DB::table('pos_orders')
                        ->where('id', $order->id)
                        ->update(['source_uuid' => (string) Str::uuid()]);
                }
            });

        DB::table('pos_orders')->update([
            'sync_status' => 'synced',
            'synced_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            foreach (['sync_error', 'last_sync_attempt_at', 'synced_at', 'sync_status', 'source_system', 'source_uuid'] as $column) {
                if (Schema::hasColumn('pos_orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
