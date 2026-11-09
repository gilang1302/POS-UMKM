<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Product;
use App\Models\StockAlert;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use App\Services\TransactionService;

class DashboardController extends Controller
{
    public function index()
    {
        $data = $this->summary();

        return view('dashboard', compact('data'));
    }

    // dipanggil JavaScript tiap beberapa detik supaya angka dashboard ikut bergerak (real-time)
    public function data()
    {
        return response()->json($this->summary());
    }

    private function summary()
    {
        // kembalikan stok dari QRIS yang tidak dibayar
        TransactionService::expireOverdue();

        $user = auth()->user();

        // omset hari ini (hanya transaksi lunas)
        $today = Transaction::paid()->visibleTo($user)->whereDate('paid_at', today());
        $revenue = (int) (clone $today)->sum('total');
        $count = (clone $today)->count();

        // grafik 7 hari terakhir
        $days = [];
        $dayRevenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = today()->subDays($i);

            $days[] = $date->translatedFormat('D d/m');
            $dayRevenue[] = (int) Transaction::paid()->visibleTo($user)
                ->whereBetween('paid_at', [$date->copy()->startOfDay(), $date->copy()->endOfDay()])
                ->sum('total');
        }

        // omset tiap cabang hari ini (owner melihat semua, kasir cabangnya saja)
        $branchRows = [];
        $branches = Branch::where('type', 'branch')->orderBy('name')->get();
        foreach ($branches as $b) {
            if (!$user->isOwner() && $b->id != $user->branch_id) {
                continue;
            }

            $branchRows[] = [
                'name' => $b->name,
                'total' => (int) Transaction::paid()->where('branch_id', $b->id)->whereDate('paid_at', today())->sum('total'),
                'count' => Transaction::paid()->where('branch_id', $b->id)->whereDate('paid_at', today())->count(),
            ];
        }

        // produk terlaris hari ini
        $topQuery = TransactionDetail::join('transactions', 'transactions.id', '=', 'transaction_details.transaction_id')
            ->where('transactions.status', 'paid')
            ->whereDate('transactions.paid_at', today());

        if (!$user->isOwner()) {
            $topQuery->where('transactions.branch_id', $user->branch_id);
        }

        $top = $topQuery
            ->selectRaw('transaction_details.product_id, SUM(transaction_details.qty) as qty')
            ->groupBy('transaction_details.product_id')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        $names = Product::whereIn('id', $top->pluck('product_id'))->pluck('name', 'id');
        $topProducts = [];
        foreach ($top as $t) {
            $topProducts[] = ['name' => $names[$t->product_id] ?? '-', 'qty' => (int) $t->qty];
        }

        // transaksi terakhir
        $latest = [];
        $latestRows = Transaction::with('branch')->visibleTo($user)->latest()->take(6)->get();
        foreach ($latestRows as $t) {
            $latest[] = [
                'invoice' => $t->invoice_no,
                'branch' => $t->branch->name,
                'total' => $t->total,
                'status' => $t->status,
                'status_label' => $t->statusLabel(),
                'time' => $t->created_at->format('H:i'),
                'url' => route('transactions.show', $t->id),
            ];
        }

        // notifikasi stok menipis yang masih terbuka
        $openAlerts = StockAlert::visibleTo($user)->open();
        $alertCount = (clone $openAlerts)->count();
        $alerts = [];
        foreach ((clone $openAlerts)->with(['branch', 'product'])->orderBy('quantity')->take(6)->get() as $a) {
            $alerts[] = [
                'product' => $a->product->name,
                'branch' => $a->branch->name,
                'quantity' => $a->quantity,
                'min_stock' => $a->min_stock,
            ];
        }

        return [
            'is_owner' => $user->isOwner(),
            'revenue' => $revenue,
            'count' => $count,
            'average' => $count > 0 ? (int) round($revenue / $count) : 0,
            'pending_qris' => Transaction::visibleTo($user)->where('status', 'pending')->count(),
            'alert_count' => $alertCount,
            'days' => $days,
            'day_revenue' => $dayRevenue,
            'branches' => $branchRows,
            'top_products' => $topProducts,
            'latest' => $latest,
            'alerts' => $alerts,
            'updated_at' => now()->format('H:i:s'),
        ];
    }
}
