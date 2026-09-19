<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {
            if (! Schema::hasColumn('pos_order_items', 'extras')) {
                $table->json('extras')->nullable()->after('price');
            }
        });
    }

    public function down(): void
    {
        Schema::table('pos_order_items', function (Blueprint $table) {
            if (Schema::hasColumn('pos_order_items', 'extras')) {
                $table->dropColumn('extras');
            }
        });
    }
};
