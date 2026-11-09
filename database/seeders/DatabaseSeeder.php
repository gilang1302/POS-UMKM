<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Models\Transaction;
use App\Models\User;
use App\Services\StockService;
use App\Services\TransactionService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // aman dijalankan ulang (misalnya saat container docker restart)
        if (User::count() > 0) {
            return;
        }

        // ===== cabang =====
        $warehouse = Branch::create(['code' => 'GDG', 'name' => 'Gudang Pusat', 'type' => 'warehouse', 'address' => 'Jl. Soekarno Hatta No. 10, Bandung']);
        $dago = Branch::create(['code' => 'DGO', 'name' => 'Cabang Dago', 'type' => 'branch', 'address' => 'Jl. Ir. H. Juanda No. 55, Bandung']);
        $buahBatu = Branch::create(['code' => 'BBT', 'name' => 'Cabang Buah Batu', 'type' => 'branch', 'address' => 'Jl. Buah Batu No. 120, Bandung']);

        // ===== user =====
        $owner = User::create([
            'name' => 'Pemilik UMKM',
            'email' => 'owner@umkm.test',
            'password' => 'password',
            'role' => 'owner',
        ]);

        $cashierDago = User::create([
            'name' => 'Kasir Dago',
            'email' => 'cashier1@umkm.test',
            'password' => 'password',
            'role' => 'cashier',
            'branch_id' => $dago->id,
        ]);

        $cashierBuahBatu = User::create([
            'name' => 'Kasir Buah Batu',
            'email' => 'cashier2@umkm.test',
            'password' => 'password',
            'role' => 'cashier',
            'branch_id' => $buahBatu->id,
        ]);

        // ===== kategori =====
        $coffee = Category::create(['name' => 'Kopi']);
        $nonCoffee = Category::create(['name' => 'Non Kopi']);
        $food = Category::create(['name' => 'Makanan']);

        // ===== produk (data sampel dulu, nanti diganti data asli dari client) =====
        $data = [
            [$coffee, 'KP001', 'Kopi Susu Gula Aren', 8000, 18000],
            [$coffee, 'KP002', 'Americano', 6000, 15000],
            [$coffee, 'KP003', 'Cappuccino', 9000, 20000],
            [$nonCoffee, 'NK001', 'Matcha Latte', 10000, 22000],
            [$nonCoffee, 'NK002', 'Teh Tarik', 5000, 12000],
            [$food, 'MK001', 'Roti Bakar Coklat', 7000, 16000],
            [$food, 'MK002', 'Kentang Goreng', 8000, 17000],
            [$food, 'MK003', 'Croissant', 9000, 20000],
        ];

        // angka acak dikunci supaya data contoh selalu sama
        mt_srand(2026);

        $products = [];
        foreach ($data as $d) {
            $product = Product::create([
                'category_id' => $d[0]->id,
                'code' => $d[1],
                'name' => $d[2],
                'cost_price' => $d[3],
                'selling_price' => $d[4],
                'unit' => 'pcs',
            ]);
            $products[] = $product;

            // stok awal tiap lokasi, dicatat sebagai barang masuk
            $this->addStock($warehouse, $product, 200, 20, $owner);
            $this->addStock($dago, $product, mt_rand(90, 130), 10, $owner);
            $this->addStock($buahBatu, $product, mt_rand(90, 130), 10, $owner);
        }

        // ===== riwayat penjualan 7 hari terakhir supaya dashboard & laporan ada isinya =====
        $shops = [[$dago, $cashierDago], [$buahBatu, $cashierBuahBatu]];

        for ($daysAgo = 6; $daysAgo >= 0; $daysAgo--) {
            foreach ($shops as [$branch, $cashier]) {
                $sales = mt_rand(8, 14);

                for ($i = 0; $i < $sales; $i++) {
                    $items = [];
                    $lines = mt_rand(1, 3);
                    for ($l = 0; $l < $lines; $l++) {
                        $items[] = [
                            'product_id' => $products[mt_rand(0, count($products) - 1)]->id,
                            'qty' => mt_rand(1, 3),
                        ];
                    }

                    $method = mt_rand(1, 10) <= 7 ? 'cash' : 'qris';
                    $transaction = TransactionService::createSale($cashier->id, $branch->id, $items, $method, 1000000);

                    if ($method == 'qris') {
                        TransactionService::markPaid($transaction->id);
                    }

                    // geser waktunya ke hari & jam kerja yang sesuai
                    $time = today()->subDays($daysAgo)->setTime(mt_rand(8, 20), mt_rand(0, 59), mt_rand(0, 59));
                    if ($time->isFuture()) {
                        $time = now()->subMinutes(mt_rand(1, 120));
                    }

                    Transaction::where('id', $transaction->id)->update([
                        'created_at' => $time,
                        'updated_at' => $time,
                        'paid_at' => $time,
                    ]);
                    StockMovement::where('reference', $transaction->invoice_no)->update(['created_at' => $time, 'updated_at' => $time]);
                }
            }
        }

        // beberapa produk di Cabang Buah Batu hasil stok opname tinggal sedikit,
        // supaya notifikasi stok menipis langsung kelihatan
        $this->lowerStock($buahBatu, $products[2], 4, $owner);
        $this->lowerStock($buahBatu, $products[5], 7, $owner);
        $this->lowerStock($dago, $products[3], 0, $owner);
    }

    // stok awal sebuah produk di sebuah lokasi
    private function addStock($branch, $product, $quantity, $min, $user)
    {
        $stock = Stock::create([
            'branch_id' => $branch->id,
            'product_id' => $product->id,
            'quantity' => 0,
            'min_stock' => $min,
        ]);

        StockService::change($stock, $quantity, 'restock', null, 'Stok awal', $user->id);
    }

    private function lowerStock($branch, $product, $quantity, $user)
    {
        $stock = Stock::where('branch_id', $branch->id)->where('product_id', $product->id)->first();
        StockService::adjustTo($stock, $quantity, 'Hasil stok opname', $user->id);
    }
}
