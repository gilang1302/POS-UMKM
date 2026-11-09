<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $branches = Branch::orderBy('name')->get();

        // kasir hanya bisa lihat cabangnya sendiri
        if ($user->isOwner()) {
            $branch_id = $request->branch_id ?? $branches->first()->id ?? null;
        } else {
            $branch_id = $user->branch_id;
        }

        $stocks = Stock::with('product.category')
            ->where('branch_id', $branch_id)
            ->get();

        return view('stock.index', compact('stocks', 'branches', 'branch_id'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        return view('stock.create', compact('branches', 'products'));
    }

    // tambah / set stok awal produk di sebuah cabang
    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
        ]);

        $stock = Stock::where('branch_id', $request->branch_id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($stock) {
            // sudah ada, jumlahnya ditambah
            $stock->quantity = $stock->quantity + $request->quantity;
            $stock->min_stock = $request->min_stock;
            $stock->save();
        } else {
            Stock::create($request->all());
        }

        return redirect()->route('stock.index', ['branch_id' => $request->branch_id])
            ->with('success', 'Stok berhasil disimpan');
    }

    // TODO: halaman edit / penyesuaian stok (stok opname)
    // TODO: transfer stok antar cabang -> StockTransferController
}
