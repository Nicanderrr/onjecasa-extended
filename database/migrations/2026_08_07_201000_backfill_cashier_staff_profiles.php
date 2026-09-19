<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pos_staff', function (Blueprint $table) {
            $table->string('number')->nullable()->change();
        });

        $linkedUserIds = DB::table('pos_staff')->whereNotNull('user_id')->pluck('user_id');

        DB::table('users')
            ->where('role', 'cashier')
            ->whereNotIn('id', $linkedUserIds)
            ->orderBy('id')
            ->each(function ($cashier) {
                DB::table('pos_staff')->insert([
                    'user_id' => $cashier->id,
                    'name' => $cashier->name,
                    'number' => null,
                    'email' => $cashier->email,
                    'pincode' => null,
                    'created_at' => $cashier->created_at ?? now(),
                    'updated_at' => now(),
                ]);
            });
    }

    public function down(): void
    {
        // Backfilled profiles are retained to avoid deleting cashier contact records.
    }
};
