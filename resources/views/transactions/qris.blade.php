@extends('layouts.app')

@section('title', 'Pembayaran QRIS')
@section('subtitle', $transaction->invoice_no . ', ' . $transaction->branch->name)

@section('content')
<div class="row justify-content-center">
  <div class="col-md-8 col-lg-5">
    <div class="card">
      <div class="card-body text-center p-4">

        @if ($transaction->status == 'pending')
          <div class="small text-muted">Total pembayaran</div>
          <div class="angka-besar mb-3">@rp($transaction->total)</div>

          <div class="qr-kotak d-inline-block border rounded p-2 mb-3" aria-label="Kode QR pembayaran">{!! $qr !!}</div>

          <div class="small text-muted">Berlaku sampai</div>
          <div class="angka-besar mb-3" id="hitung-mundur" data-berakhir="{{ $transaction->expires_at->getTimestamp() * 1000 }}">--:--</div>

          <div class="alert alert-warning text-start">
            Ini simulasi pembayaran, belum terhubung ke payment gateway. Setelah pelanggan selesai
            "membayar", tekan tombol di bawah untuk mencatat pembayaran.
          </div>

          <form action="{{ route('qris.pay', $transaction->id) }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-primary w-100"><i class="fas fa-check"></i> Simulasikan pembayaran berhasil</button>
          </form>

          <form action="{{ route('qris.cancel', $transaction->id) }}" method="POST" class="mt-2" data-konfirmasi="Batalkan pembayaran ini? Stok barang akan dikembalikan.">
            @csrf
            <button type="submit" class="btn btn-danger w-100">Batalkan pesanan</button>
          </form>

        @else
          <div class="angka-besar mb-2">{{ $transaction->statusLabel() }}</div>
          <p>
            @if ($transaction->status == 'expired')
              Waktu pembayaran habis, stok barang sudah dikembalikan.
            @else
              Pesanan ini dibatalkan, stok barang sudah dikembalikan.
            @endif
          </p>
          <a href="{{ route('pos') }}" class="btn btn-primary">Buat transaksi baru</a>
        @endif

      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@if ($transaction->status == 'pending')
  <script src="{{ asset('js/qris.js') }}"></script>
@endif
@endpush
