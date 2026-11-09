<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Stock;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TransactionController extends Controller
{
    // halaman kasir (POS)
    public function pos(Request $request)
    {
        $user = auth()->user();
        $branches = Branch::where('type', 'branch')->get();

        // kasir pakai cabangnya sendiri, owner boleh pilih
        if ($user->isOwner()) {
            $branch_id = $request->branch_id ?? $branches->first()->id ?? null;
        } else {
            $branch_id = $user->branch_id;
        }

        // hanya tampilkan produk yang stoknya masih ada
        $stocks = Stock::with('product')
            ->where('branch_id', $branch_id)
            ->where('quantity', '>', 0)
            ->get();

        return view('transactions.pos', compact('stocks', 'branches', 'branch_id'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'paid_amount' => 'required|integer|min:0',
            'payment_method' => 'required|in:cash,qris',
        ]);

        // TODO: simulasi pembayaran QRIS
        if ($request->payment_method == 'qris') {
            return back()->with('error', 'Pembayaran QRIS belum tersedia')->withInput();
        }

        try {
            $transaction = DB::transaction(function () use ($request) {
                $total = 0;
                $details = [];

                foreach ($request->items as $item) {
                    // lockForUpdate supaya baris stok dikunci selama transaksi berjalan
                    // jadi kasir lain di cabang yang sama harus nunggu (cegah stok minus)
                    $stock = Stock::with('product')
                        ->where('branch_id', $request->branch_id)
                        ->where('product_id', $item['product_id'])
                        ->lockForUpdate()
                        ->first();

                    if (!$stock) {
                        throw new \Exception('Produk belum punya stok di cabang ini');
                    }

                    if ($stock->quantity < $item['qty']) {
                        throw new \Exception('Stok ' . $stock->product->name . ' tidak cukup (sisa ' . $stock->quantity . ')');
                    }

                    $subtotal = $stock->product->selling_price * $item['qty'];
                    $total += $subtotal;

                    $stock->quantity = $stock->quantity - $item['qty'];
                    $stock->save();

                    $details[] = [
                        'product_id' => $stock->product_id,
                        'qty' => $item['qty'],
                        'price' => $stock->product->selling_price,
                        'subtotal' => $subtotal,
                    ];
                }

                if ($request->paid_amount < $total) {
                    throw new \Exception('Uang bayar kurang dari total belanja');
                }

                $transaction = Transaction::create([
                    'invoice_no' => 'INV-' . date('YmdHis') . '-' . rand(100, 999),
                    'branch_id' => $request->branch_id,
                    'user_id' => auth()->id(),
                    'total' => $total,
                    'paid_amount' => $request->paid_amount,
                    'change_amount' => $request->paid_amount - $total,
                    'payment_method' => $request->payment_method,
                ]);

                foreach ($details as $d) {
                    $d['transaction_id'] = $transaction->id;
                    TransactionDetail::create($d);
                }

                return $transaction;
            });
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('transactions.show', $transaction->id)
            ->with('success', 'Transaksi berhasil disimpan');
    }

    public function index()
    {
        $user = auth()->user();

        $query = Transaction::with(['branch', 'user'])->latest();

        if (!$user->isOwner()) {
            $query->where('branch_id', $user->branch_id);
        }

        $transactions = $query->paginate(10);

        return view('transactions.index', compact('transactions'));
    }

    public function show($id)
    {
        $transaction = Transaction::with(['branch', 'user', 'details.product'])->findOrFail($id);

        // TODO: tombol cetak struk (versi thermal 58mm)
        return view('transactions.show', compact('transaction'));
    }
}
