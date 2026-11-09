<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\StockAlert;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    public function index()
    {
        $branches = Branch::orderBy('name')->get();
        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        return view('branches.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|max:10|unique:branches,code',
            'name' => 'required',
            'type' => 'required|in:branch,warehouse',
        ]);

        Branch::create($request->all());

        return redirect()->route('branches.index')->with('success', 'Cabang berhasil ditambahkan');
    }

    public function edit($id)
    {
        $branch = Branch::findOrFail($id);
        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, $id)
    {
        $branch = Branch::findOrFail($id);

        $request->validate([
            'code' => 'required|max:10|unique:branches,code,' . $id,
            'name' => 'required',
            'type' => 'required|in:branch,warehouse',
        ]);

        $branch->update($request->all());

        return redirect()->route('branches.index')->with('success', 'Data cabang berhasil diubah');
    }

    public function destroy($id)
    {
        $branch = Branch::findOrFail($id);

        // cabang yang sudah punya transaksi / riwayat stok / stok tersisa tidak boleh dihapus
        $hasTransactions = $branch->transactions()->exists();
        $hasMovements = StockMovement::where('branch_id', $branch->id)->exists();
        $hasStock = $branch->stocks()->where('quantity', '>', 0)->exists();
        $hasUsers = User::where('branch_id', $branch->id)->exists();

        if ($hasTransactions || $hasMovements || $hasStock || $hasUsers) {
            return back()->with('error', 'Cabang masih punya transaksi, stok, atau pengguna sehingga tidak bisa dihapus');
        }

        // baris stok kosong dan notifikasinya ikut dibersihkan
        StockAlert::where('branch_id', $branch->id)->delete();
        $branch->stocks()->delete();
        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Cabang dihapus');
    }
}
