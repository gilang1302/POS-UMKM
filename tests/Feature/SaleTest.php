<?php

namespace Tests\Feature;

use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Services\TransactionService;

class SaleTest extends PosTestCase
{
    private function items($qty)
    {
        return [['product_id' => $this->product->id, 'qty' => $qty]];
    }

    public function test_penjualan_tunai_mengurangi_stok_dan_menghitung_kembalian(): void
    {
        $this->actingAs($this->cashier)->post('/pos', [
            'branch_id' => $this->shopA->id,
            'items' => $this->items(2),
            'payment_method' => 'cash',
            'paid_amount' => 50000,
        ])->assertRedirect();

        $trx = Transaction::first();
        $this->assertEquals('paid', $trx->status);
        $this->assertEquals(36000, $trx->total);
        $this->assertEquals(14000, $trx->change_amount);
        $this->assertEquals(8, $this->qty($this->shopA));
        $this->assertEquals(1, StockMovement::where('type', 'sale')->count());
    }

    public function test_uang_kurang_ditolak_dan_stok_tidak_berubah(): void
    {
        $this->actingAs($this->cashier)->post('/pos', [
            'branch_id' => $this->shopA->id,
            'items' => $this->items(2),
            'payment_method' => 'cash',
            'paid_amount' => 1000,
        ])->assertSessionHas('error');

        $this->assertEquals(10, $this->qty($this->shopA));
        $this->assertEquals(0, Transaction::count());
    }

    public function test_tidak_bisa_menjual_lebih_dari_stok(): void
    {
        $this->actingAs($this->cashier)->post('/pos', [
            'branch_id' => $this->shopA->id,
            'items' => $this->items(11),
            'payment_method' => 'cash',
            'paid_amount' => 500000,
        ])->assertSessionHas('error');

        $this->assertEquals(10, $this->qty($this->shopA));
        $this->assertEquals(0, Transaction::count());
    }

    public function test_kasir_tidak_bisa_menjual_atas_nama_cabang_lain(): void
    {
        $this->putStock($this->shopB, 10);

        $this->actingAs($this->cashier)->post('/pos', [
            'branch_id' => $this->shopB->id,
            'items' => $this->items(1),
            'payment_method' => 'cash',
            'paid_amount' => 20000,
        ]);

        // tetap tercatat di cabang kasir, stok cabang B aman
        $this->assertEquals(9, $this->qty($this->shopA));
        $this->assertEquals(10, $this->qty($this->shopB));
    }

    public function test_barang_yang_sama_dua_kali_digabung(): void
    {
        $this->actingAs($this->cashier)->post('/pos', [
            'branch_id' => $this->shopA->id,
            'items' => [
                ['product_id' => $this->product->id, 'qty' => 3],
                ['product_id' => $this->product->id, 'qty' => 4],
            ],
            'payment_method' => 'cash',
            'paid_amount' => 200000,
        ]);

        $this->assertEquals(3, $this->qty($this->shopA));
        $this->assertEquals(1, Transaction::first()->details()->count());
    }

    public function test_stok_menipis_membuat_notifikasi_dan_hilang_setelah_barang_masuk(): void
    {
        // stok 10, minimum 5, jual 6 -> sisa 4
        TransactionService::createSale($this->cashier->id, $this->shopA->id, $this->items(6), 'cash', 200000);
        $this->assertEquals(1, StockAlert::open()->count());

        // tambah stok lewat transfer dari gudang
        $this->actingAs($this->owner)->post('/stock-transfer', [
            'from_branch_id' => $this->warehouse->id,
            'to_branch_id' => $this->shopA->id,
            'items' => $this->items(20),
        ]);
        $this->assertEquals(0, StockAlert::open()->count());
    }

    public function test_kasir_tidak_bisa_melihat_transaksi_cabang_lain(): void
    {
        $this->putStock($this->shopB, 10);
        $trx = TransactionService::createSale($this->owner->id, $this->shopB->id, $this->items(1), 'cash', 20000);

        $this->actingAs($this->cashier)->get('/transactions/' . $trx->id)->assertStatus(404);
        $this->actingAs($this->owner)->get('/transactions/' . $trx->id)->assertOk();
    }
}
