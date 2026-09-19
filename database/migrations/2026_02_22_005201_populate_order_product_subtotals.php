<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        // Update existing records to set subtotal = price * quantity where subtotal is null or 0
        DB::table('order_products')
            ->whereNull('subtotal')
            ->orWhere('subtotal', 0)
            ->update(['subtotal' => DB::raw('price * quantity')]);
    }

    public function down()
    {
        // No need to revert
    }
};