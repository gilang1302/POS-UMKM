@extends('layouts.app')

@section('title', 'Detail transaksi')
@section('subtitle', $transaction->invoice_no)

@section('actions')
  @if ($transaction->status == 'paid')
    <a href="{{ route('transactions.struk', $transaction->id) }}" target="_blank" class="btn btn-outline-secondary"><i class="fas fa-print"></i> Cetak struk</a>
  @endif
  <a href="{{ route('pos') }}" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Transaksi baru</a>
@endsection

@section('content')
@if ($transaction->status == 'pending')
  <div class="alert alert-warning">
    <i class="fas fa-clock"></i>
    Pembayaran QRIS belum selesai. <a href="{{ route('qris.show', $transaction->id) }}">Lanjutkan pembayaran</a>
  </div>
@endif

<div class="row">
  <div class="col-lg-8">
  <div class="card">
    <div class="card-header"><div class="card-title">Barang</div></div>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr>
            <th>Produk</th>
            <th class="text-end">Harga</th>
            <th class="text-end">Jumlah</th>
            <th class="text-end">Subtotal</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($transaction->details as $d)
            <tr>
              <td>{{ $d->product->name }}</td>
              <td class="text-end">@rp($d->price)</td>
              <td class="text-end">{{ $d->qty }}</td>
              <td class="text-end">@rp($d->subtotal)</td>
            </tr>
          @endforeach
        </tbody>
        <tfoot>
          <tr class="fw-bold"><td colspan="3" class="text-end">Total</td><td class="text-end">@rp($transaction->total)</td></tr>
        </tfoot>
      </table>
    </div>
  </div>
  </div>

  <div class="col-lg-4">
  <div class="card">
    <div class="card-header"><div class="card-title">Informasi</div></div>
    <div class="card-body">
      <dl class="row mb-0">
        <dt class="col-5">Status</dt>
        <dd class="col-7"><span class="badge {{ $transaction->badgeClass() }}">{{ $transaction->statusLabel() }}</span></dd>
        <dt class="col-5">Waktu</dt>
        <dd class="col-7">{{ $transaction->created_at->format('d/m/Y H:i') }}</dd>
        <dt class="col-5">Cabang</dt>
        <dd class="col-7">{{ $transaction->branch->name }}</dd>
        <dt class="col-5">Kasir</dt>
        <dd class="col-7">{{ $transaction->user->name }}</dd>
        <dt class="col-5">Metode bayar</dt>
        <dd class="col-7">{{ $transaction->payment_method == 'qris' ? 'QRIS' : 'Tunai' }}</dd>
        @if ($transaction->status == 'paid')
          <dt class="col-5">Dibayar</dt>
          <dd class="col-7">@rp($transaction->paid_amount)</dd>
          <dt class="col-5">Kembalian</dt>
          <dd class="col-7">@rp($transaction->change_amount)</dd>
        @endif
        @if ($transaction->payment_ref)
          <dt class="col-5">Kode QRIS</dt>
          <dd class="col-7">{{ $transaction->payment_ref }}</dd>
        @endif
      </dl>
    </div>
  </div>
  </div>
</div>
@endsection
