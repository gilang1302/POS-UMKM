<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Models\User;
use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Pengujian race condition stok.
 *
 * Contoh: stok tinggal 3, lalu 8 kasir menekan "bayar" di detik yang sama.
 * Yang benar: tepat 3 transaksi berhasil, 5 ditolak, stok akhir 0 (tidak minus).
 *
 * php artisan stok:uji-balapan --proses=8 --stok=3
 */
class UjiBalapanStok extends Command
{
    protected $signature = 'stok:uji-balapan {--proses=8 : jumlah kasir yang membeli bersamaan} {--stok=3 : stok awal produk uji}';

    protected $description = 'Uji race condition: banyak transaksi bersamaan untuk stok yang terbatas';

    public function handle()
    {
        $processes = (int) $this->option('proses');
        $initialStock = (int) $this->option('stok');

        $branch = Branch::where('type', 'branch')->first();
        $user = User::where('role', 'owner')->first();
        $category = Category::first();

        if (!$branch || !$user || !$category) {
            $this->error('Data cabang / pengguna / kategori belum ada. Jalankan php artisan migrate --seed dulu.');
            return self::FAILURE;
        }

        // produk khusus uji, dihapus lagi di akhir
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'UJI' . rand(1000, 9999),
            'name' => 'Produk Uji Balapan',
            'cost_price' => 1000,
            'selling_price' => 2000,
            'unit' => 'pcs',
        ]);

        Stock::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => $initialStock,
            'min_stock' => 0,
        ]);

        $this->info("Cabang {$branch->name}, stok awal {$initialStock}, {$processes} kasir membeli 1 pcs bersamaan...");

        // jalankan semua proses hampir bersamaan
        $running = [];
        for ($i = 1; $i <= $processes; $i++) {
            $p = new Process([PHP_BINARY, base_path('artisan'), 'stok:beli-uji', $product->id, $branch->id, $user->id]);
            $p->setTimeout(60);
            $p->start();
            $running[] = $p;
        }

        $success = 0;
        $rejected = 0;
        foreach ($running as $i => $p) {
            $p->wait();
            $output = trim($p->getOutput());

            if (str_starts_with($output, 'OK')) {
                $success++;
            } else {
                $rejected++;
            }
            $this->line('  kasir ' . ($i + 1) . ': ' . ($output ?: trim($p->getErrorOutput())));
        }

        $finalStock = Stock::where('branch_id', $branch->id)->where('product_id', $product->id)->value('quantity');
        $expected = min($processes, $initialStock);

        $this->newLine();
        $this->line("Berhasil : {$success} (seharusnya {$expected})");
        $this->line("Ditolak  : {$rejected}");
        $this->line("Stok akhir: {$finalStock} (seharusnya " . ($initialStock - $expected) . ')');

        $passed = $success == $expected && $finalStock == $initialStock - $expected && $finalStock >= 0;

        // bersihkan data uji
        $transactionIds = TransactionDetail::where('product_id', $product->id)->pluck('transaction_id');
        TransactionDetail::where('product_id', $product->id)->delete();
        Transaction::whereIn('id', $transactionIds)->delete();
        StockMovement::where('product_id', $product->id)->delete();
        StockAlert::where('product_id', $product->id)->delete();
        Stock::where('product_id', $product->id)->delete();
        $product->delete();

        if ($passed) {
            $this->info('LULUS: stok tidak pernah minus dan tidak ada penjualan ganda.');
            return self::SUCCESS;
        }

        $this->error('GAGAL: jumlah transaksi atau stok akhir tidak sesuai.');
        return self::FAILURE;
    }
}
