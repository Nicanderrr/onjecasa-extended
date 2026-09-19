<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained('product_pages')->onDelete('cascade');
            $table->string('username')->nullable();
            $table->string('name')->nullable();
            $table->string('description')->nullable();
            $table->string('contents')->nullable();
            $table->string('price')->nullable();
            $table->integer('quantity')->nullable();
            // $table->string('message'); // The notification message
            // $table->timestamp('read_at')->nullable(); // Timestamp for when the notification was read
            // $table->string('status')->default('in_cart'); // could be 'in_cart', 'ordered', etc.
            // $table->string('product_name'); // optional if you want to store it for easy reference
            // $table->decimal('total_price', 10, 2); // storing total price




            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carts');
    }
};
