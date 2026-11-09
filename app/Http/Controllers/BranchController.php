<?php

namespace App\Http\Controllers;

use App\Models\Branch;
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

        // TODO: cek dulu apakah cabang masih punya stok / transaksi
        $branch->delete();

        return redirect()->route('branches.index')->with('success', 'Cabang dihapus');
    }
}
