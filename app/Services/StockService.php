<?php

namespace App\Services;

use App\Exceptions\StokException;
use App\Models\Stock;
use App\Models\StockAlert;
use App\Models\StockMovement;

/**
 * Semua perubahan jumlah stok lewat sini, jadi aturannya selalu sama:
 *  1. baris stok dikunci dulu (lockForUpdate) selama transaksi database berjalan
 *  2. pengurangan memakai UPDATE ... WHERE quantity >= jumlah, jadi stok tidak
 *     mungkin jadi minus walaupun dua kasir menekan bayar di waktu yang sama
 *  3. setiap perubahan dicatat di stock_movements
 *  4. notifikasi stok menipis dibuat / ditutup otomatis
 */
class StockService
{
    // ambil baris stok dan kunci sampai transaksi database selesai
    // HARUS dipanggil di dalam DB::transaction()
    public static function lock($branchId, $productId)
    {
        return Stock::where('branch_id', $branchId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();
    }

    // tambah (amount positif) atau kurangi (amount negatif) stok
    public static function change(Stock $stock, $amount, $type, $reference = null, $note = null, $userId = null)
    {
        $amount = (int) $amount;

        if ($amount < 0) {
            $need = -$amount;

            $affected = Stock::where('id', $stock->id)
                ->where('quantity', '>=', $need)
                ->decrement('quantity', $need);

            if ($affected == 0) {
                $stock->loadMissing('product');
                throw new StokException('Stok ' . $stock->product->name . ' tidak cukup');
            }
        } elseif ($amount > 0) {
            Stock::where('id', $stock->id)->increment('quantity', $amount);
        }

        // baca ulang supaya dapat angka terbaru dari database
        $stock->refresh();

        StockMovement::create([
            'branch_id' => $stock->branch_id,
            'product_id' => $stock->product_id,
            'user_id' => $userId ?? auth()->id(),
            'type' => $type,
            'qty_change' => $amount,
            'qty_after' => $stock->quantity,
            'reference' => $reference,
            'note' => $note,
        ]);

        self::syncAlert($stock);

        return $stock;
    }

    // set stok jadi angka hasil hitung fisik (stok opname)
    public static function adjustTo(Stock $stock, $newQuantity, $note = null, $userId = null)
    {
        $diff = (int) $newQuantity - $stock->quantity;

        if ($diff != 0) {
            self::change($stock, $diff, 'adjustment', null, $note, $userId);
        }

        return $stock;
    }

    // cocokkan notifikasi stok menipis dengan kondisi stok sekarang
    public static function syncAlert(Stock $stock)
    {
        $open = StockAlert::where('branch_id', $stock->branch_id)
            ->where('product_id', $stock->product_id)
            ->whereNull('resolved_at')
            ->first();

        if ($stock->quantity <= $stock->min_stock) {
            if ($open) {
                $open->update(['quantity' => $stock->quantity, 'min_stock' => $stock->min_stock]);
            } else {
                StockAlert::create([
                    'branch_id' => $stock->branch_id,
                    'product_id' => $stock->product_id,
                    'quantity' => $stock->quantity,
                    'min_stock' => $stock->min_stock,
                ]);
            }
        } elseif ($open) {
            // stok sudah aman lagi
            $open->update(['resolved_at' => now()]);
        }
    }
}
