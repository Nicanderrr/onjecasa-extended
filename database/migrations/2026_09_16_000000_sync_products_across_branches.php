<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('branch_product_exclusions')) {
            Schema::create('branch_product_exclusions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('branch_id')->constrained('branches')->cascadeOnDelete();
                $table->string('product_code', 100);
                $table->foreignId('removed_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();

                $table->unique(['branch_id', 'product_code']);
            });
        }

        if (Schema::hasTable('pos_products')) {
            $this->dropIndexIfExists('pos_products', 'pos_products_code_unique');
            $this->addIndexIfMissing('pos_products', 'pos_products_branch_id_code_unique', 'unique', ['branch_id', 'code']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('pos_products')) {
            $this->dropIndexIfExists('pos_products', 'pos_products_branch_id_code_unique');
            $this->addIndexIfMissing('pos_products', 'pos_products_code_unique', 'unique', ['code']);
        }

        Schema::dropIfExists('branch_product_exclusions');
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        $exists = collect(Schema::getIndexes($table))->contains('name', $index);

        if ($exists) {
            Schema::table($table, fn (Blueprint $table) => $table->dropIndex($index));
        }
    }

    private function addIndexIfMissing(string $table, string $index, string $type, array $columns): void
    {
        $exists = collect(Schema::getIndexes($table))->contains('name', $index);

        if ($exists) {
            return;
        }

        Schema::table($table, function (Blueprint $table) use ($type, $columns, $index) {
            $table->{$type}($columns, $index);
        });
    }
};
