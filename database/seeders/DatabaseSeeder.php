<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ===== cabang =====
        $warehouse = Branch::create(['code' => 'GDG', 'name' => 'Gudang Pusat', 'type' => 'warehouse', 'address' => 'Jl. Soekarno Hatta No. 10, Bandung']);
        $dago = Branch::create(['code' => 'DGO', 'name' => 'Cabang Dago', 'type' => 'branch', 'address' => 'Jl. Ir. H. Juanda No. 55, Bandung']);
        $buahBatu = Branch::create(['code' => 'BBT', 'name' => 'Cabang Buah Batu', 'type' => 'branch', 'address' => 'Jl. Buah Batu No. 120, Bandung']);

        // ===== user =====
        User::create([
            'name' => 'Pemilik UMKM',
            'email' => 'owner@umkm.test',
            'password' => 'password',
            'role' => 'owner',
        ]);

        User::create([
            'name' => 'Kasir Dago',
            'email' => 'cashier1@umkm.test',
            'password' => 'password',
            'role' => 'cashier',
            'branch_id' => $dago->id,
        ]);

        User::create([
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

        foreach ($data as $d) {
            $product = Product::create([
                'category_id' => $d[0]->id,
                'code' => $d[1],
                'name' => $d[2],
                'cost_price' => $d[3],
                'selling_price' => $d[4],
                'unit' => 'pcs',
            ]);

            // stok awal tiap lokasi
            Stock::create(['branch_id' => $warehouse->id, 'product_id' => $product->id, 'quantity' => 100, 'min_stock' => 20]);
            Stock::create(['branch_id' => $dago->id, 'product_id' => $product->id, 'quantity' => rand(10, 40), 'min_stock' => 10]);
            Stock::create(['branch_id' => $buahBatu->id, 'product_id' => $product->id, 'quantity' => rand(3, 30), 'min_stock' => 10]);
        }
    }
}
