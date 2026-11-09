<?php

namespace App\Http\Controllers;

use App\Exceptions\StokException;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Stock;
use App\Models\Transaction;
use App\Services\TransactionService;
use App\Support\QrCode;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    // halaman kasir (POS)
    public function pos(Request $request)
    {
        // kembalikan stok dari QRIS yang tidak dibayar
        TransactionService::expireOverdue();

        $user = auth()->user();
        $branches = Branch::where('type', 'branch')->orderBy('name')->get();

        // kasir pakai cabangnya sendiri, owner boleh pilih
        if ($user->isOwner()) {
            $branch_id = $branches->contains('id', $request->branch_id) ? $request->branch_id : ($branches->first()->id ?? null);
        } else {
            $branch_id = $user->branch_id;
        }

        // hanya tampilkan produk yang stoknya masih ada
        $stocks = Stock::with('product.category')
            ->where('branch_id', $branch_id)
            ->where('quantity', '>', 0)
            ->get()
            ->sortBy(function ($s) {
                return $s->product->name;
            })
            ->values();

        $categories = Category::orderBy('name')->get();
        $branch = Branch::find($branch_id);

        return view('transactions.pos', compact('stocks', 'branches', 'branch_id', 'branch', 'categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'branch_id' => 'required|exists:branches,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.qty' => 'required|integer|min:1',
            'payment_method' => 'required|in:cash,qris',
            'paid_amount' => 'required_if:payment_method,cash|nullable|integer|min:0',
        ]);

        $user = auth()->user();

        // kasir tidak boleh menjual atas nama cabang lain
        $branch_id = $user->isOwner() ? $request->branch_id : $user->branch_id;

        $isShop = Branch::where('id', $branch_id)->where('type', 'branch')->exists();
        if (!$isShop) {
            return back()->with('error', 'Penjualan hanya bisa dilakukan di cabang, bukan di gudang')->withInput();
        }

        try {
            $transaction = TransactionService::createSale(
                $user->id,
                $branch_id,
                $request->items,
                $request->payment_method,
                (int) $request->paid_amount
            );
        } catch (StokException $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        // QRIS: lanjut ke halaman scan, tunai: langsung ke detail
        if ($transaction->payment_method == 'qris') {
            return redirect()->route('qris.show', $transaction->id);
        }

        return redirect()->route('transactions.show', $transaction->id)
            ->with('success', 'Transaksi berhasil disimpan');
    }

    public function index(Request $request)
    {
        TransactionService::expireOverdue();

        $user = auth()->user();

        $query = Transaction::with(['branch', 'user'])->visibleTo($user)->latest();

        if ($request->filled('q')) {
            $query->where('invoice_no', 'like', '%' . $request->q . '%');
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($user->isOwner() && $request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        $transactions = $query->paginate(15)->withQueryString();
        $branches = Branch::where('type', 'branch')->orderBy('name')->get();

        return view('transactions.index', compact('transactions', 'branches'));
    }

    public function show($id)
    {
        $transaction = $this->findTransaction($id);

        return view('transactions.show', compact('transaction'));
    }

    // struk siap cetak (lebar kertas thermal 58mm)
    public function struk($id)
    {
        $transaction = $this->findTransaction($id);

        if ($transaction->status != 'paid') {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('error', 'Struk hanya bisa dicetak untuk transaksi yang sudah lunas');
        }

        return view('transactions.struk', compact('transaction'));
    }

    // ---------- simulasi pembayaran QRIS ----------

    public function qris($id)
    {
        TransactionService::expireOverdue();

        $transaction = $this->findTransaction($id);

        if ($transaction->status == 'paid') {
            return redirect()->route('transactions.show', $transaction->id);
        }

        // kode QR hanya dibuat selama transaksi masih menunggu pembayaran
        $qr = null;
        if ($transaction->status == 'pending') {
            $qr = QrCode::svg('POS-UMKM|' . $transaction->payment_ref . '|' . $transaction->invoice_no . '|' . $transaction->total);
        }

        return view('transactions.qris', compact('transaction', 'qr'));
    }

    // tombol "simulasikan pelanggan sudah bayar"
    public function qrisPay($id)
    {
        $transaction = $this->findTransaction($id);
        $transaction = TransactionService::markPaid($transaction->id);

        if ($transaction->status == 'paid') {
            return redirect()->route('transactions.show', $transaction->id)
                ->with('success', 'Pembayaran QRIS diterima');
        }

        return redirect()->route('qris.show', $transaction->id)
            ->with('error', 'Waktu pembayaran sudah habis, stok dikembalikan. Buat transaksi baru.');
    }

    public function qrisCancel($id)
    {
        $transaction = $this->findTransaction($id);
        TransactionService::cancelPending($transaction->id);

        return redirect()->route('pos')->with('success', 'Pembayaran QRIS dibatalkan, stok dikembalikan');
    }

    // ambil transaksi, kasir cuma boleh yang dari cabangnya
    private function findTransaction($id)
    {
        return Transaction::with(['branch', 'user', 'details.product'])
            ->visibleTo(auth()->user())
            ->findOrFail($id);
    }
}
