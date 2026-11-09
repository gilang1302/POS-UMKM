<?php

namespace App\Http\Controllers;

use App\Exceptions\StokException;
use App\Models\Branch;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Services\TransferService;
use Illuminate\Http\Request;

class StockTransferController extends Controller
{
    public function index()
    {
        $transfers = StockTransfer::with(['fromBranch', 'toBranch', 'user', 'items.product'])
            ->latest()
            ->paginate(10);

        return view('transfers.index', compact('transfers'));
    }

    public function create()
    {
        $branches = Branch::orderBy('name')->get();

        // daftar stok per lokasi, dipakai JavaScript untuk mengisi pilihan produk
        $stockMap = [];
        $stocks = Stock::with('product')->where('quantity', '>', 0)->get();
        foreach ($stocks as $s) {
            $stockMap[$s->branch_id][] = [
                'product_id' => $s->product_id,
                'code' => $s->product->code,
                'name' => $s->product->name,
                'qty' => $s->quantity,
                'unit' => $s->product->unit,
            ];
        }

        return view('transfers.create', compact('branches', 'stockMap'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'from_branch_id' => 'required|exists:branches,id',
            'to_branch_id' => 'required|exists:branches,id|different:from_branch_id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'note' => 'nullable|max:200',
        ], [
            'to_branch_id.different' => 'Lokasi tujuan harus berbeda dengan lokasi asal',
            'items.required' => 'Tambahkan minimal satu produk yang mau dipindahkan',
        ]);

        try {
            $transfer = TransferService::create(
                auth()->id(),
                $request->from_branch_id,
                $request->to_branch_id,
                $request->items,
                $request->note
            );
        } catch (StokException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('stock-transfer.index')
            ->with('success', 'Transfer ' . $transfer->code . ' berhasil, stok kedua lokasi sudah diperbarui');
    }
}
