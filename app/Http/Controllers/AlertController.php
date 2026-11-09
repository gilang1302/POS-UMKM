<?php

namespace App\Http\Controllers;

use App\Models\StockAlert;

class AlertController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // yang masih menipis
        $open = StockAlert::visibleTo($user)->open()->with(['branch', 'product'])->latest()->get();

        // riwayat yang sudah teratasi
        $resolved = StockAlert::visibleTo($user)->whereNotNull('resolved_at')
            ->with(['branch', 'product'])->latest('resolved_at')->take(15)->get();

        return view('alerts.index', compact('open', 'resolved'));
    }

    public function read($id)
    {
        StockAlert::visibleTo(auth()->user())->where('id', $id)->update(['read_at' => now()]);

        return back();
    }

    public function readAll()
    {
        StockAlert::visibleTo(auth()->user())->whereNull('read_at')->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca');
    }
}
