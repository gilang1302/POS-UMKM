<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->get();
        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate(['name' => 'required|max:100']);

        Category::create(['name' => $request->name]);

        return back()->with('success', 'Kategori ditambahkan');
    }

    public function update(Request $request, $id)
    {
        $request->validate(['name' => 'required|max:100']);

        Category::findOrFail($id)->update(['name' => $request->name]);

        return back()->with('success', 'Kategori diubah');
    }

    public function destroy($id)
    {
        $category = Category::withCount('products')->findOrFail($id);

        if ($category->products_count > 0) {
            return back()->with('error', 'Kategori masih dipakai produk, tidak bisa dihapus');
        }

        $category->delete();

        return back()->with('success', 'Kategori dihapus');
    }
}
