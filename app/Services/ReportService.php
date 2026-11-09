<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;

class ReportService
{
    /**
     * Susun data laporan keuangan.
     * $type     : 'daily' atau 'monthly'
     * $period   : 'Y-m-d' untuk harian, 'Y-m' untuk bulanan
     * $branchId : null = semua cabang
     */
    public static function build($type, $period, $branchId = null)
    {
        if ($type == 'monthly') {
            $start = Carbon::createFromFormat('Y-m', $period)->startOfMonth();
            $end = $start->copy()->endOfMonth();
            $periodLabel = $start->translatedFormat('F Y');
        } else {
            $start = Carbon::createFromFormat('Y-m-d', $period)->startOfDay();
            $end = $start->copy()->endOfDay();
            $periodLabel = $start->translatedFormat('d F Y');
        }

        $query = Transaction::paid()
            ->with(['branch', 'user'])
            ->whereBetween('paid_at', [$start, $end])
            ->orderBy('paid_at');

        if ($branchId) {
            $query->where('branch_id', $branchId);
        }

        $transactions = $query->get();
        $branchName = $branchId ? Branch::find($branchId)->name ?? '-' : 'Semua cabang';

        // ringkasan
        $total = $transactions->sum('total');
        $count = $transactions->count();

        $details = TransactionDetail::with('product')
            ->whereIn('transaction_id', $transactions->pluck('id'))
            ->get();

        $cogs = 0;
        $products = [];
        foreach ($details as $d) {
            $cogs += $d->cost_price * $d->qty;

            $key = $d->product_id;
            if (!isset($products[$key])) {
                $products[$key] = [
                    'code' => $d->product->code,
                    'name' => $d->product->name,
                    'qty' => 0,
                    'revenue' => 0,
                ];
            }
            $products[$key]['qty'] += $d->qty;
            $products[$key]['revenue'] += $d->subtotal;
        }
        // produk terlaris di atas
        usort($products, function ($a, $b) {
            return $b['qty'] <=> $a['qty'];
        });

        // per cabang
        $perBranch = [];
        foreach ($transactions->groupBy('branch_id') as $group) {
            $perBranch[] = [
                'name' => $group->first()->branch->name,
                'count' => $group->count(),
                'total' => $group->sum('total'),
            ];
        }

        // per hari (dipakai untuk laporan bulanan)
        $perDay = [];
        foreach ($transactions->groupBy(function ($t) {
            return $t->paid_at->format('Y-m-d');
        }) as $date => $group) {
            $perDay[] = [
                'date' => Carbon::parse($date)->format('d/m/Y'),
                'count' => $group->count(),
                'total' => $group->sum('total'),
            ];
        }

        // per metode bayar
        $cash = $transactions->where('payment_method', 'cash');
        $qris = $transactions->where('payment_method', 'qris');

        $rows = [];
        foreach ($transactions as $t) {
            $rows[] = [
                'invoice' => $t->invoice_no,
                'time' => $t->paid_at->format('d/m/Y H:i'),
                'branch' => $t->branch->name,
                'cashier' => $t->user->name,
                'method' => strtoupper($t->payment_method),
                'total' => $t->total,
            ];
        }

        return [
            'type' => $type,
            'title' => $type == 'monthly' ? 'Laporan Keuangan Bulanan' : 'Laporan Keuangan Harian',
            'period' => $periodLabel,
            'branch' => $branchName,
            'generated_at' => now()->format('d/m/Y H:i'),
            'summary' => [
                'count' => $count,
                'total' => $total,
                'cogs' => $cogs,
                'profit' => $total - $cogs,
                'average' => $count > 0 ? (int) round($total / $count) : 0,
                'cash_total' => $cash->sum('total'),
                'cash_count' => $cash->count(),
                'qris_total' => $qris->sum('total'),
                'qris_count' => $qris->count(),
            ],
            'per_branch' => $perBranch,
            'per_day' => $perDay,
            'products' => array_values($products),
            'transactions' => $rows,
        ];
    }

    public static function fileName($report, $extension)
    {
        $name = $report['type'] == 'monthly' ? 'laporan-bulanan' : 'laporan-harian';
        $period = str_replace([' ', '/'], '-', strtolower($report['period']));

        return $name . '-' . $period . '.' . $extension;
    }
}
