<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockMovement;
use App\Services\StockService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
            ->get()
            ->sortBy(function ($s) {
                return $s->product->name;
            })
            ->values();

        $branch = Branch::find($branch_id);

        return view('stock.index', compact('stocks', 'branches', 'branch_id', 'branch'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();
        $products = Product::orderBy('name')->get();
        return view('stock.create', compact('branches', 'products'));
    }

    // barang masuk: tambah stok produk di sebuah cabang / gudang
    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'product_id' => 'required|exists:products,id',
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'note' => 'nullable|max:200',
        ]);

        DB::transaction(function () use ($request) {
            $stock = StockService::lock($request->branch_id, $request->product_id);

            if (!$stock) {
                $stock = Stock::create([
                    'branch_id' => $request->branch_id,
                    'product_id' => $request->product_id,
                    'quantity' => 0,
                    'min_stock' => $request->min_stock,
                ]);
            }

            $stock->min_stock = $request->min_stock;
            $stock->save();

            if ($request->quantity > 0) {
                StockService::change($stock, $request->quantity, 'restock', null, $request->note);
            } else {
                // hanya mengubah batas minimum
                StockService::syncAlert($stock);
            }
        });

        return redirect()->route('stock.index', ['branch_id' => $request->branch_id])
            ->with('success', 'Stok berhasil disimpan');
    }

    // stok opname: samakan stok di sistem dengan hasil hitung fisik
    public function adjust($id)
    {
        $stock = Stock::with(['product', 'branch'])->findOrFail($id);
        return view('stock.adjust', compact('stock'));
    }

    public function adjustUpdate(Request $request, $id)
    {
        $request->validate([
            'quantity' => 'required|integer|min:0',
            'min_stock' => 'required|integer|min:0',
            'note' => 'nullable|max:200',
        ]);

        DB::transaction(function () use ($request, $id) {
            $stock = Stock::lockForUpdate()->findOrFail($id);

            $stock->min_stock = $request->min_stock;
            $stock->save();

            StockService::adjustTo($stock, $request->quantity, $request->note ?: 'Stok opname');
            StockService::syncAlert($stock);
        });

        $stock = Stock::findOrFail($id);

        return redirect()->route('stock.index', ['branch_id' => $stock->branch_id])
            ->with('success', 'Stok berhasil disesuaikan');
    }

    // kartu stok: semua perubahan stok
    public function history(Request $request)
    {
        $user = auth()->user();
        $branches = Branch::orderBy('name')->get();

        $query = StockMovement::with(['branch', 'product', 'user'])->latest('id');

        if ($user->isOwner()) {
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }
        } else {
            $query->where('branch_id', $user->branch_id);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $movements = $query->paginate(20)->withQueryString();

        return view('stock.history', compact('movements', 'branches'));
    }
}
