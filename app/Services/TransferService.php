<?php

namespace App\Services;

use App\Exceptions\StokException;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\StockTransferItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TransferService
{
    /**
     * Pindahkan stok dari satu lokasi ke lokasi lain (cabang / gudang).
     * $items = [['product_id' => 1, 'qty' => 10], ...]
     */
    public static function create($userId, $fromId, $toId, $items, $note = null)
    {
        if ($fromId == $toId) {
            throw new StokException('Lokasi asal dan tujuan tidak boleh sama');
        }

        // gabungkan produk yang sama, urutkan supaya urutan kunci selalu sama
        $merged = [];
        foreach ($items as $item) {
            $id = (int) $item['product_id'];
            $merged[$id] = ($merged[$id] ?? 0) + (int) $item['qty'];
        }
        ksort($merged);

        return DB::transaction(function () use ($userId, $fromId, $toId, $merged, $note) {
            $code = 'TRF-' . date('ymd-His') . '-' . strtoupper(Str::random(3));

            $transfer = StockTransfer::create([
                'code' => $code,
                'from_branch_id' => $fromId,
                'to_branch_id' => $toId,
                'user_id' => $userId,
                'note' => $note,
            ]);

            foreach ($merged as $productId => $qty) {
                // kunci baris asal dan tujuan sekaligus, selalu urut berdasarkan id cabang.
                // kalau ada dua transfer berlawanan arah (A->B dan B->A) keduanya
                // mengunci dengan urutan yang sama, jadi tidak saling menunggu selamanya
                $rows = Stock::where('product_id', $productId)
                    ->whereIn('branch_id', [$fromId, $toId])
                    ->orderBy('branch_id')
                    ->lockForUpdate()
                    ->get();

                $source = $rows->firstWhere('branch_id', $fromId);
                $target = $rows->firstWhere('branch_id', $toId);

                if (!$source) {
                    throw new StokException('Ada produk yang tidak punya stok di lokasi asal');
                }

                if ($source->quantity < $qty) {
                    $source->loadMissing('product');
                    throw new StokException('Stok ' . $source->product->name . ' di lokasi asal tidak cukup (sisa ' . $source->quantity . ')');
                }

                if (!$target) {
                    // produk belum pernah ada di lokasi tujuan, buat baris stoknya
                    $target = Stock::create([
                        'branch_id' => $toId,
                        'product_id' => $productId,
                        'quantity' => 0,
                        'min_stock' => $source->min_stock,
                    ]);
                }

                StockService::change($source, -$qty, 'transfer_out', $code, null, $userId);
                StockService::change($target, $qty, 'transfer_in', $code, null, $userId);

                StockTransferItem::create([
                    'stock_transfer_id' => $transfer->id,
                    'product_id' => $productId,
                    'qty' => $qty,
                ]);
            }

            return $transfer;
        }, 3);
    }
}
