<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\Transaction;

class DashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // kalau kasir, datanya cuma cabang dia sendiri
        $transactionQuery = Transaction::whereDate('created_at', today());
        $stockQuery = Stock::whereColumn('quantity', '<=', 'min_stock');

        if (!$user->isOwner()) {
            $transactionQuery->where('branch_id', $user->branch_id);
            $stockQuery->where('branch_id', $user->branch_id);
        }

        $today_revenue = $transactionQuery->sum('total');
        $today_transactions = $transactionQuery->count();
        $low_stock_count = $stockQuery->count();

        $total_branches = Branch::count();
        $total_products = Product::count();

        $latest_transactions = Transaction::with('branch')->latest()->take(5)->get();

        return view('dashboard', compact(
            'today_revenue',
            'today_transactions',
            'low_stock_count',
            'total_branches',
            'total_products',
            'latest_transactions'
        ));
    }
}
