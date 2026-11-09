<?php

namespace App\Http\Controllers;

// controller sementara buat menu yang belum dikerjakan
class ComingSoonController extends Controller
{
    public function stockTransfer()
    {
        return view('coming-soon', ['title' => 'Transfer Stok Antar Cabang']);
    }

    public function reports()
    {
        return view('coming-soon', ['title' => 'Laporan Keuangan (PDF / Excel)']);
    }
}
