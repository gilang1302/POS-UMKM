<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            // paid / pending / cancelled / expired
            $table->string('status', 20)->default('paid')->after('payment_method');
            $table->string('payment_ref', 40)->nullable()->after('status');
            $table->timestamp('paid_at')->nullable()->after('payment_ref');
            $table->timestamp('expires_at')->nullable()->after('paid_at');
        });

        Schema::table('transaction_details', function (Blueprint $table) {
            // harga beli disimpan saat transaksi, dipakai untuk hitung laba kotor di laporan
            $table->integer('cost_price')->default(0)->after('price');
        });

        // data lama dianggap sudah lunas
        DB::table('transactions')->update(['paid_at' => DB::raw('created_at')]);
    }

    public function down(): void
    {
        Schema::table('transaction_details', function (Blueprint $table) {
            $table->dropColumn('cost_price');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['status', 'payment_ref', 'paid_at', 'expires_at']);
        });
    }
};
