<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Services\ReportService;
use App\Support\SimplePdf;
use App\Support\XlsxWriter;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$type, $period, $branch_id] = $this->readFilter($request);

        $report = ReportService::build($type, $period, $branch_id);
        $branches = Branch::where('type', 'branch')->orderBy('name')->get();

        $date = $type == 'daily' ? $period : date('Y-m-d');
        $month = $type == 'monthly' ? $period : date('Y-m');

        return view('reports.index', compact('report', 'branches', 'type', 'date', 'month', 'branch_id'));
    }

    public function pdf(Request $request)
    {
        [$type, $period, $branch_id] = $this->readFilter($request);
        $report = ReportService::build($type, $period, $branch_id);
        $s = $report['summary'];

        $pdf = new SimplePdf($report['title']);
        $pdf->brandHeader($report['title'], [
            'Periode: ' . $report['period'],
            'Lokasi: ' . $report['branch'],
            'Dibuat: ' . $report['generated_at'],
        ]);

        $pdf->heading('Ringkasan');
        $pdf->keyValue([
            ['Jumlah transaksi', (string) $s['count']],
            ['Omset (penjualan lunas)', $this->rp($s['total']), true],
            ['Harga pokok barang terjual', $this->rp($s['cogs'])],
            ['Laba kotor', $this->rp($s['profit']), true],
            ['Rata-rata per transaksi', $this->rp($s['average'])],
            ['Tunai', $this->rp($s['cash_total']) . ' (' . $s['cash_count'] . ' transaksi)'],
            ['QRIS', $this->rp($s['qris_total']) . ' (' . $s['qris_count'] . ' transaksi)'],
        ]);

        if (count($report['per_branch']) > 0) {
            $rows = [];
            foreach ($report['per_branch'] as $b) {
                $rows[] = [$b['name'], (string) $b['count'], $this->rp($b['total'])];
            }
            $pdf->heading('Omset per cabang');
            $pdf->table(['Cabang', 'Transaksi', 'Omset'], $rows, [4, 1.5, 2.5], ['left', 'right', 'right']);
        }

        if ($type == 'monthly' && count($report['per_day']) > 0) {
            $rows = [];
            foreach ($report['per_day'] as $d) {
                $rows[] = [$d['date'], (string) $d['count'], $this->rp($d['total'])];
            }
            $pdf->heading('Omset per hari');
            $pdf->table(['Tanggal', 'Transaksi', 'Omset'], $rows, [4, 1.5, 2.5], ['left', 'right', 'right']);
        }

        if (count($report['products']) > 0) {
            $rows = [];
            foreach ($report['products'] as $p) {
                $rows[] = [$p['code'], $p['name'], (string) $p['qty'], $this->rp($p['revenue'])];
            }
            $pdf->heading('Produk terjual');
            $pdf->table(['Kode', 'Produk', 'Terjual', 'Pendapatan'], $rows, [1.5, 4, 1.3, 2.2], ['left', 'left', 'right', 'right']);
        }

        $pdf->heading('Daftar transaksi');
        if (count($report['transactions']) == 0) {
            $pdf->keyValue([['Tidak ada transaksi lunas pada periode ini', '']]);
        } else {
            $rows = [];
            foreach ($report['transactions'] as $t) {
                $rows[] = [$t['invoice'], $t['time'], $t['branch'], $t['cashier'], $t['method'], $this->rp($t['total'])];
            }
            $rows[] = ['Total', '', '', '', '', $this->rp($s['total'])];
            $pdf->table(
                ['Invoice', 'Waktu', 'Cabang', 'Kasir', 'Metode', 'Total'],
                $rows,
                [3.1, 2.4, 2.4, 2.2, 1.3, 2.1],
                ['left', 'left', 'left', 'left', 'left', 'right'],
                true
            );
        }

        return response($pdf->output(), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . ReportService::fileName($report, 'pdf') . '"',
        ]);
    }

    public function excel(Request $request)
    {
        [$type, $period, $branch_id] = $this->readFilter($request);
        $report = ReportService::build($type, $period, $branch_id);
        $s = $report['summary'];

        $xlsx = new XlsxWriter();

        // sheet 1: ringkasan
        $summary = [
            [['v' => $report['title'], 'style' => 'title']],
            ['Periode', $report['period']],
            ['Lokasi', $report['branch']],
            ['Dibuat', $report['generated_at']],
            [],
            [['v' => 'Ringkasan', 'style' => 'header'], ['v' => '', 'style' => 'header']],
            ['Jumlah transaksi', ['v' => $s['count'], 'style' => 'int']],
            [['v' => 'Omset (penjualan lunas)', 'style' => 'bold'], ['v' => $s['total'], 'style' => 'moneybold']],
            ['Harga pokok barang terjual', ['v' => $s['cogs'], 'style' => 'money']],
            [['v' => 'Laba kotor', 'style' => 'bold'], ['v' => $s['profit'], 'style' => 'moneybold']],
            ['Rata-rata per transaksi', ['v' => $s['average'], 'style' => 'money']],
            ['Tunai', ['v' => $s['cash_total'], 'style' => 'money']],
            ['QRIS', ['v' => $s['qris_total'], 'style' => 'money']],
        ];
        $xlsx->addSheet('Ringkasan', $summary, [32, 24]);

        // sheet 2: per cabang (harian) atau per hari (bulanan)
        $per = [[
            ['v' => 'Cabang', 'style' => 'header'],
            ['v' => 'Transaksi', 'style' => 'header'],
            ['v' => 'Omset', 'style' => 'header'],
        ]];
        foreach ($report['per_branch'] as $b) {
            $per[] = [$b['name'], ['v' => $b['count'], 'style' => 'int'], ['v' => $b['total'], 'style' => 'money']];
        }
        $xlsx->addSheet('Per cabang', $per, [28, 12, 18]);

        if ($type == 'monthly') {
            $perDay = [[
                ['v' => 'Tanggal', 'style' => 'header'],
                ['v' => 'Transaksi', 'style' => 'header'],
                ['v' => 'Omset', 'style' => 'header'],
            ]];
            foreach ($report['per_day'] as $d) {
                $perDay[] = [$d['date'], ['v' => $d['count'], 'style' => 'int'], ['v' => $d['total'], 'style' => 'money']];
            }
            $xlsx->addSheet('Per hari', $perDay, [16, 12, 18]);
        }

        // sheet produk
        $products = [[
            ['v' => 'Kode', 'style' => 'header'],
            ['v' => 'Produk', 'style' => 'header'],
            ['v' => 'Terjual', 'style' => 'header'],
            ['v' => 'Pendapatan', 'style' => 'header'],
        ]];
        foreach ($report['products'] as $p) {
            $products[] = [$p['code'], $p['name'], ['v' => $p['qty'], 'style' => 'int'], ['v' => $p['revenue'], 'style' => 'money']];
        }
        $xlsx->addSheet('Produk', $products, [12, 30, 12, 18]);

        // sheet transaksi
        $list = [[
            ['v' => 'Invoice', 'style' => 'header'],
            ['v' => 'Waktu', 'style' => 'header'],
            ['v' => 'Cabang', 'style' => 'header'],
            ['v' => 'Kasir', 'style' => 'header'],
            ['v' => 'Metode', 'style' => 'header'],
            ['v' => 'Total', 'style' => 'header'],
        ]];
        foreach ($report['transactions'] as $t) {
            $list[] = [$t['invoice'], $t['time'], $t['branch'], $t['cashier'], $t['method'], ['v' => $t['total'], 'style' => 'money']];
        }
        $xlsx->addSheet('Transaksi', $list, [26, 18, 22, 20, 10, 16]);

        return response($xlsx->output(), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . ReportService::fileName($report, 'xlsx') . '"',
        ]);
    }

    // baca pilihan filter dari form, kalau kosong / salah format pakai hari ini
    private function readFilter(Request $request)
    {
        $request->validate([
            'type' => 'nullable|in:daily,monthly',
            'date' => 'nullable|date_format:Y-m-d',
            'month' => 'nullable|date_format:Y-m',
            'branch_id' => 'nullable|exists:branches,id',
        ]);

        $type = $request->type == 'monthly' ? 'monthly' : 'daily';

        if ($type == 'monthly') {
            $period = $request->month ?: date('Y-m');
        } else {
            $period = $request->date ?: date('Y-m-d');
        }

        return [$type, $period, $request->branch_id ?: null];
    }

    private function rp($number)
    {
        return 'Rp ' . number_format($number, 0, ',', '.');
    }
}
