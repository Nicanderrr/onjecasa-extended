<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            if (!Schema::hasColumn('pos_payments', 'paystack_reference')) {
                $table->string('paystack_reference')->nullable()->after('amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_payments', function (Blueprint $table) {
            if (Schema::hasColumn('pos_payments', 'paystack_reference')) {
                $table->dropColumn('paystack_reference');
            }
        });
    }
};
