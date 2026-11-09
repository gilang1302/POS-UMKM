<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Struk {{ $transaction->invoice_no }}</title>
  <link rel="stylesheet" href="{{ asset('css/struk.css') }}">
</head>
<body>
  <div class="alat">
    <button type="button" onclick="window.print()">Cetak struk</button>
    <a href="{{ route('transactions.show', $transaction->id) }}">Kembali</a>
  </div>

  <div class="struk">
    <div class="tengah">
      <div class="nama-toko">POS UMKM</div>
      <div>{{ $transaction->branch->name }}</div>
      @if ($transaction->branch->address)
        <div>{{ $transaction->branch->address }}</div>
      @endif
    </div>

    <div class="garis"></div>

    <div class="baris"><span>No</span><span>{{ $transaction->invoice_no }}</span></div>
    <div class="baris"><span>Tanggal</span><span>{{ $transaction->paid_at ? $transaction->paid_at->format('d/m/Y H:i') : $transaction->created_at->format('d/m/Y H:i') }}</span></div>
    <div class="baris"><span>Kasir</span><span>{{ $transaction->user->name }}</span></div>

    <div class="garis"></div>

    @foreach ($transaction->details as $d)
      <div class="item">{{ $d->product->name }}</div>
      <div class="baris">
        <span>{{ $d->qty }} x @rp($d->price)</span>
        <span>@rp($d->subtotal)</span>
      </div>
    @endforeach

    <div class="garis"></div>

    <div class="baris tebal"><span>TOTAL</span><span>@rp($transaction->total)</span></div>
    <div class="baris"><span>{{ $transaction->payment_method == 'qris' ? 'QRIS' : 'Tunai' }}</span><span>@rp($transaction->paid_amount)</span></div>
    <div class="baris"><span>Kembali</span><span>@rp($transaction->change_amount)</span></div>

    <div class="garis"></div>

    <div class="tengah">Terima kasih sudah berbelanja</div>
  </div>
</body>
</html>
