<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // catatan setiap perubahan stok (kartu stok), supaya selisih stok bisa ditelusuri
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('product_id')->constrained('products');
            $table->foreignId('user_id')->nullable()->constrained('users');
            // sale / sale_cancel / restock / transfer_in / transfer_out / adjustment
            $table->string('type', 20);
            $table->integer('qty_change');
            $table->integer('qty_after');
            $table->string('reference', 40)->nullable();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['branch_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
