<?php


use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOrderProductsTable extends Migration
{
    public function up()
    {
        Schema::create('order_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('username');      // Column for the user's name
            $table->unsignedBigInteger('order_id');  // Foreign key removed
            $table->unsignedBigInteger('product_id'); // Foreign key removed
            $table->string('product_name')->default('');  // Column for the product name
            $table->integer('quantity');
            $table->decimal('price', 10, 2);
            $table->decimal('total', 10, 2);
            $table->string('phone');
            $table->string('address');
            $table->timestamps();

                // Foreign key constraint
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // Optionally, add indexes to these columns for faster lookups
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('order_products');
    }
}
