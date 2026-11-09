@extends('layouts.app')

@section('title', 'Detail Transaksi')

@section('content')
<div class="card">
  <div class="card-body">
    <table class="table table-borderless w-50">
      <tr><td>No Invoice</td><td>: {{ $transaction->invoice_no }}</td></tr>
      <tr><td>Tanggal</td><td>: {{ $transaction->created_at->format('d/m/Y H:i') }}</td></tr>
      <tr><td>Cabang</td><td>: {{ $transaction->branch->name }}</td></tr>
      <tr><td>Kasir</td><td>: {{ $transaction->user->name }}</td></tr>
      <tr><td>Metode Bayar</td><td>: {{ strtoupper($transaction->payment_method) }}</td></tr>
    </table>

    <table class="table table-bordered mt-3">
      <thead>
        <tr>
          <th>No</th>
          <th>Produk</th>
          <th>Harga</th>
          <th>Qty</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        @foreach ($transaction->details as $d)
          <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $d->product->name }}</td>
            <td>Rp {{ number_format($d->price, 0, ',', '.') }}</td>
            <td>{{ $d->qty }}</td>
            <td>Rp {{ number_format($d->subtotal, 0, ',', '.') }}</td>
          </tr>
        @endforeach
      </tbody>
      <tfoot>
        <tr><th colspan="4" class="text-end">Total</th><th>Rp {{ number_format($transaction->total, 0, ',', '.') }}</th></tr>
        <tr><th colspan="4" class="text-end">Bayar</th><th>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</th></tr>
        <tr><th colspan="4" class="text-end">Kembalian</th><th>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</th></tr>
      </tfoot>
    </table>

    <a href="{{ route('pos') }}" class="btn btn-primary">Transaksi Baru</a>
    <a href="{{ route('transactions.index') }}" class="btn btn-secondary">Riwayat</a>
    {{-- TODO: tombol cetak struk --}}
  </div>
</div>
@endsection
