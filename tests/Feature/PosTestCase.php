<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

// kelas dasar: menyiapkan data kecil yang dipakai semua tes
abstract class PosTestCase extends TestCase
{
    use RefreshDatabase;

    protected $owner;
    protected $cashier;
    protected $warehouse;
    protected $shopA;
    protected $shopB;
    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->warehouse = Branch::create(['code' => 'GDG', 'name' => 'Gudang', 'type' => 'warehouse']);
        $this->shopA = Branch::create(['code' => 'AAA', 'name' => 'Cabang A', 'type' => 'branch']);
        $this->shopB = Branch::create(['code' => 'BBB', 'name' => 'Cabang B', 'type' => 'branch']);

        $this->owner = User::create([
            'name' => 'Owner', 'email' => 'owner@test.com', 'password' => 'password', 'role' => 'owner',
        ]);
        $this->cashier = User::create([
            'name' => 'Kasir A', 'email' => 'kasir@test.com', 'password' => 'password',
            'role' => 'cashier', 'branch_id' => $this->shopA->id,
        ]);

        $category = Category::create(['name' => 'Kopi']);
        $this->product = Product::create([
            'category_id' => $category->id, 'code' => 'KP01', 'name' => 'Kopi Susu',
            'cost_price' => 8000, 'selling_price' => 18000, 'unit' => 'pcs',
        ]);

        $this->putStock($this->warehouse, 50);
        $this->putStock($this->shopA, 10);
    }

    protected function putStock($branch, $qty, $min = 5)
    {
        return Stock::create([
            'branch_id' => $branch->id, 'product_id' => $this->product->id,
            'quantity' => $qty, 'min_stock' => $min,
        ]);
    }

    protected function qty($branch)
    {
        return Stock::where('branch_id', $branch->id)->where('product_id', $this->product->id)->value('quantity');
    }
}
