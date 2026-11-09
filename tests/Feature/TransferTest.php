<?php

namespace Tests\Feature;

use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\StockTransfer;

class TransferTest extends PosTestCase
{
    public function test_transfer_gudang_ke_cabang(): void
    {
        $this->actingAs($this->owner)->post('/stock-transfer', [
            'from_branch_id' => $this->warehouse->id,
            'to_branch_id' => $this->shopA->id,
            'items' => [['product_id' => $this->product->id, 'qty' => 15]],
            'note' => 'restock',
        ])->assertRedirect();

        $this->assertEquals(35, $this->qty($this->warehouse));
        $this->assertEquals(25, $this->qty($this->shopA));
        $this->assertEquals(1, StockTransfer::count());
        $this->assertEquals(1, StockMovement::where('type', 'transfer_out')->count());
        $this->assertEquals(1, StockMovement::where('type', 'transfer_in')->count());
    }

    public function test_transfer_ke_cabang_yang_belum_punya_baris_stok(): void
    {
        $this->actingAs($this->owner)->post('/stock-transfer', [
            'from_branch_id' => $this->warehouse->id,
            'to_branch_id' => $this->shopB->id,
            'items' => [['product_id' => $this->product->id, 'qty' => 5]],
        ]);

        $this->assertEquals(5, $this->qty($this->shopB));
    }

    public function test_transfer_melebihi_stok_ditolak_semua(): void
    {
        $this->actingAs($this->owner)->post('/stock-transfer', [
            'from_branch_id' => $this->shopA->id,
            'to_branch_id' => $this->shopB->id,
            'items' => [['product_id' => $this->product->id, 'qty' => 99]],
        ])->assertSessionHas('error');

        $this->assertEquals(10, $this->qty($this->shopA));
        $this->assertEquals(0, Stock::where('branch_id', $this->shopB->id)->count());
        $this->assertEquals(0, StockTransfer::count());
    }

    public function test_asal_dan_tujuan_tidak_boleh_sama(): void
    {
        $this->actingAs($this->owner)->post('/stock-transfer', [
            'from_branch_id' => $this->shopA->id,
            'to_branch_id' => $this->shopA->id,
            'items' => [['product_id' => $this->product->id, 'qty' => 1]],
        ])->assertSessionHasErrors('to_branch_id');
    }
}
