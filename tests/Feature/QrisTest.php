<?php

namespace Tests\Feature;

use App\Models\Transaction;
use App\Services\TransactionService;

class QrisTest extends PosTestCase
{
    private function buatQris($qty = 3)
    {
        return TransactionService::createSale(
            $this->cashier->id, $this->shopA->id,
            [['product_id' => $this->product->id, 'qty' => $qty]], 'qris', 0
        );
    }

    public function test_qris_menahan_stok_sampai_dibayar(): void
    {
        $trx = $this->buatQris(3);

        $this->assertEquals('pending', $trx->status);
        $this->assertEquals(7, $this->qty($this->shopA));

        $this->actingAs($this->cashier)->get('/transactions/' . $trx->id . '/qris')->assertOk();
        $this->actingAs($this->cashier)->post('/transactions/' . $trx->id . '/qris/bayar')
            ->assertRedirect('/transactions/' . $trx->id);

        $this->assertEquals('paid', $trx->fresh()->status);
        $this->assertEquals(7, $this->qty($this->shopA));
    }

    public function test_batal_qris_mengembalikan_stok(): void
    {
        $trx = $this->buatQris(3);

        $this->actingAs($this->cashier)->post('/transactions/' . $trx->id . '/qris/batal');

        $this->assertEquals('cancelled', $trx->fresh()->status);
        $this->assertEquals(10, $this->qty($this->shopA));
    }

    public function test_qris_kedaluwarsa_mengembalikan_stok(): void
    {
        $trx = $this->buatQris(3);
        Transaction::where('id', $trx->id)->update(['expires_at' => now()->subMinute()]);

        TransactionService::expireOverdue();

        $this->assertEquals('expired', $trx->fresh()->status);
        $this->assertEquals(10, $this->qty($this->shopA));

        // membayar setelah kedaluwarsa tidak boleh menjadi lunas
        $this->actingAs($this->cashier)->post('/transactions/' . $trx->id . '/qris/bayar');
        $this->assertEquals('expired', $trx->fresh()->status);
        $this->assertEquals(10, $this->qty($this->shopA));
    }

    public function test_dua_qris_tidak_bisa_menahan_stok_yang_sama(): void
    {
        $this->buatQris(6);

        $this->expectException(\App\Exceptions\StokException::class);
        $this->buatQris(6);
    }
}
