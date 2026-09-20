<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->string('customer_phone', 30)->nullable()->after('customer_name');
            $table->string('receipt_token', 80)->nullable()->unique()->after('customer_phone');
            $table->timestamp('receipt_sms_sent_at')->nullable()->after('receipt_token');
        });
    }

    public function down(): void
    {
        Schema::table('pos_orders', function (Blueprint $table) {
            $table->dropUnique(['receipt_token']);
            $table->dropColumn(['customer_phone', 'receipt_token', 'receipt_sms_sent_at']);
        });
    }
};
