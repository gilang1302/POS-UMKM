<?php

namespace App\Services;

use App\Exceptions\StokException;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransactionService
{
    // lama waktu kode QRIS berlaku (menit)
    const QRIS_MINUTES = 5;

    public static function makeInvoiceNo()
    {
        do {
            $no = 'INV-' . date('ymd-His') . '-' . strtoupper(Str::random(3));
        } while (Transaction::where('invoice_no', $no)->exists());

        return $no;
    }

    /**
     * Simpan transaksi penjualan.
     * $items = [['product_id' => 1, 'qty' => 2], ...]
     * Tunai  -> langsung lunas.
     * QRIS   -> status pending, stok langsung "ditahan" (dikurangi) supaya tidak
     *           dijual ke orang lain. Kalau tidak dibayar sampai waktunya habis,
     *           stok dikembalikan (lihat expireOverdue).
     */
    public static function createSale($userId, $branchId, $items, $method, $paidAmount)
    {
        // gabungkan produk yang sama lalu urutkan berdasarkan id produk.
        // urutan kunci yang selalu sama mencegah deadlock antar kasir
        $merged = [];
        foreach ($items as $item) {
            $id = (int) $item['product_id'];
            $merged[$id] = ($merged[$id] ?? 0) + (int) $item['qty'];
        }
        ksort($merged);

        return DB::transaction(function () use ($userId, $branchId, $merged, $method, $paidAmount) {
            $invoice = self::makeInvoiceNo();
            $total = 0;
            $details = [];

            foreach ($merged as $productId => $qty) {
                $stock = StockService::lock($branchId, $productId);

                if (!$stock) {
                    throw new StokException('Ada produk yang belum punya stok di cabang ini');
                }

                $product = $stock->product;

                if ($stock->quantity < $qty) {
                    throw new StokException('Stok ' . $product->name . ' tidak cukup (sisa ' . $stock->quantity . ')');
                }

                $subtotal = $product->selling_price * $qty;
                $total += $subtotal;

                StockService::change($stock, -$qty, 'sale', $invoice, null, $userId);

                $details[] = [
                    'product_id' => $productId,
                    'qty' => $qty,
                    'price' => $product->selling_price,
                    'cost_price' => $product->cost_price,
                    'subtotal' => $subtotal,
                ];
            }

            $isCash = $method == 'cash';

            if ($isCash && $paidAmount < $total) {
                throw new StokException('Uang bayar kurang dari total belanja');
            }

            $transaction = Transaction::create([
                'invoice_no' => $invoice,
                'branch_id' => $branchId,
                'user_id' => $userId,
                'total' => $total,
                'paid_amount' => $isCash ? $paidAmount : 0,
                'change_amount' => $isCash ? $paidAmount - $total : 0,
                'payment_method' => $method,
                'status' => $isCash ? 'paid' : 'pending',
                'payment_ref' => $isCash ? null : 'QRIS' . strtoupper(Str::random(10)),
                'paid_at' => $isCash ? now() : null,
                'expires_at' => $isCash ? null : now()->addMinutes(self::QRIS_MINUTES),
            ]);

            foreach ($details as $d) {
                $d['transaction_id'] = $transaction->id;
                TransactionDetail::create($d);
            }

            return $transaction;
        }, 3);
    }

    // simulasi: pelanggan sudah membayar lewat QRIS
    public static function markPaid($id)
    {
        return DB::transaction(function () use ($id) {
            $t = Transaction::lockForUpdate()->findOrFail($id);

            if ($t->status != 'pending') {
                return $t;
            }

            // kalau waktunya sudah habis, batalkan dan kembalikan stok
            if ($t->expires_at && $t->expires_at->isPast()) {
                self::release($t, 'expired');
                return $t;
            }

            $t->update([
                'status' => 'paid',
                'paid_amount' => $t->total,
                'change_amount' => 0,
                'paid_at' => now(),
            ]);

            return $t;
        });
    }

    // kasir membatalkan pembayaran QRIS yang masih pending
    public static function cancelPending($id)
    {
        return DB::transaction(function () use ($id) {
            $t = Transaction::lockForUpdate()->findOrFail($id);

            if ($t->status == 'pending') {
                self::release($t, 'cancelled');
            }

            return $t;
        });
    }

    // batalkan semua QRIS pending yang sudah lewat waktu dan kembalikan stoknya.
    // dipanggil setiap membuka dashboard / kasir / riwayat, jadi tidak perlu scheduler
    public static function expireOverdue()
    {
        $ids = Transaction::where('status', 'pending')
            ->where('expires_at', '<', now())
            ->pluck('id');

        foreach ($ids as $id) {
            DB::transaction(function () use ($id) {
                $t = Transaction::lockForUpdate()->find($id);

                if ($t && $t->status == 'pending' && $t->expires_at->isPast()) {
                    self::release($t, 'expired');
                }
            });
        }
    }

    // kembalikan stok transaksi pending lalu ubah statusnya. HARUS di dalam DB::transaction()
    private static function release(Transaction $t, $newStatus)
    {
        $details = $t->details()->orderBy('product_id')->get();

        foreach ($details as $d) {
            $stock = StockService::lock($t->branch_id, $d->product_id);

            if ($stock) {
                StockService::change($stock, $d->qty, 'sale_cancel', $t->invoice_no, null, $t->user_id);
            }
        }

        $t->update(['status' => $newStatus]);
    }
}
