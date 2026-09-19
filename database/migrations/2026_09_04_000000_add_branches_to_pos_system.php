<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('branches')) {
            Schema::create('branches', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('code')->unique();
                $table->string('phone')->nullable();
                $table->text('address')->nullable();
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('branch_user')) {
            Schema::create('branch_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['branch_id', 'user_id']);
            });
        }

        $defaultBranchId = DB::table('branches')->where('code', 'MAIN')->value('id');
        if (! $defaultBranchId) {
            $defaultBranchId = DB::table('branches')->insertGetId([
                'name' => 'Main Store',
                'code' => 'MAIN',
                'phone' => null,
                'address' => 'Primary storefront branch',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        foreach (['pos_products', 'pos_categories', 'pos_orders', 'pos_staff', 'pos_audit_trails', 'orders', 'order_products'] as $tableName) {
            if (Schema::hasTable($tableName) && ! Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->foreignId('branch_id')->nullable()->after('id')->constrained('branches')->nullOnDelete();
                });

                DB::table($tableName)->whereNull('branch_id')->update(['branch_id' => $defaultBranchId]);
            }
        }

        $userIds = DB::table('users')
            ->whereIn('role', ['admin', 'cashier', 'superadmin'])
            ->orWhereIn('is_admin', [1, 2])
            ->pluck('id');

        foreach ($userIds as $userId) {
            DB::table('branch_user')->updateOrInsert(
                ['branch_id' => $defaultBranchId, 'user_id' => $userId],
                ['created_at' => now(), 'updated_at' => now()]
            );
        }
    }

    public function down(): void
    {
        foreach (['order_products', 'orders', 'pos_audit_trails', 'pos_staff', 'pos_orders', 'pos_categories', 'pos_products'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'branch_id')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropConstrainedForeignId('branch_id');
                });
            }
        }

        Schema::dropIfExists('branch_user');
        Schema::dropIfExists('branches');
    }
};
