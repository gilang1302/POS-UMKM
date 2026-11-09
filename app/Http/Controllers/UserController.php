<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('branch')->orderBy('role')->orderBy('name')->get();
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $branches = Branch::where('type', 'branch')->orderBy('name')->get();
        return view('users.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required|in:owner,cashier',
            'branch_id' => 'required_if:role,cashier|nullable|exists:branches,id',
        ], [
            'branch_id.required_if' => 'Kasir harus ditempatkan di salah satu cabang',
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password,
            'role' => $request->role,
            'branch_id' => $request->role == 'cashier' ? $request->branch_id : null,
        ]);

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        $branches = Branch::where('type', 'branch')->orderBy('name')->get();
        return view('users.edit', compact('user', 'branches'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|max:100',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'nullable|min:6',
            'role' => 'required|in:owner,cashier',
            'branch_id' => 'required_if:role,cashier|nullable|exists:branches,id',
        ], [
            'branch_id.required_if' => 'Kasir harus ditempatkan di salah satu cabang',
        ]);

        // jangan sampai satu-satunya owner diubah jadi kasir
        if ($user->isOwner() && $request->role != 'owner' && User::where('role', 'owner')->count() <= 1) {
            return back()->with('error', 'Harus ada minimal satu owner')->withInput();
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->branch_id = $request->role == 'cashier' ? $request->branch_id : null;

        // password diisi hanya kalau mau diganti
        if ($request->filled('password')) {
            $user->password = $request->password;
        }

        $user->save();

        return redirect()->route('users.index')->with('success', 'Data pengguna berhasil diubah');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->id == auth()->id()) {
            return back()->with('error', 'Akun yang sedang dipakai tidak bisa dihapus');
        }

        // pengguna yang sudah punya transaksi dipertahankan supaya riwayat tetap utuh
        if (Transaction::where('user_id', $user->id)->exists()) {
            return back()->with('error', 'Pengguna sudah punya transaksi dan tidak bisa dihapus');
        }

        try {
            $user->delete();
        } catch (\Exception $e) {
            return back()->with('error', 'Pengguna tidak bisa dihapus karena sudah dipakai di data lain');
        }

        return redirect()->route('users.index')->with('success', 'Pengguna dihapus');
    }
}
