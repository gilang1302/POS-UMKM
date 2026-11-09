@extends('layouts.app')

@section('title', 'Kasir')
@section('subtitle', $branch ? $branch->name : 'Belum ada cabang')

@section('actions')
  @if (auth()->user()->isOwner())
    <form action="{{ route('pos') }}" method="GET" class="m-0">
      <label class="visually-hidden" for="pilih-cabang">Cabang</label>
      <select class="form-select" name="branch_id" id="pilih-cabang" onchange="this.form.submit()">
        @foreach ($branches as $b)
          <option value="{{ $b->id }}" {{ $branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
        @endforeach
      </select>
    </form>
  @endif
@endsection

@section('content')
<div class="row">

  {{-- daftar produk --}}
  <div class="col-lg-8">
    <div class="card">
      <div class="card-body">
        <div class="input-group mb-3">
          <span class="input-group-text"><i class="fas fa-search"></i></span>
          <input type="text" class="form-control" id="cari-produk" placeholder="Cari produk" aria-label="Cari produk" autocomplete="off">
        </div>

        <div class="mb-3 d-flex flex-wrap gap-2">
          <button type="button" class="btn btn-sm btn-primary btn-kategori" data-cat="">Semua</button>
          @foreach ($categories as $c)
            <button type="button" class="btn btn-sm btn-outline-primary btn-kategori" data-cat="{{ $c->name }}">{{ $c->name }}</button>
          @endforeach
        </div>

        @if ($stocks->count() == 0)
          <div class="alert alert-warning mb-0">Belum ada stok di cabang ini. Tambahkan lewat menu Stok.</div>
        @endif

        <div class="row g-3 daftar-produk">
          @foreach ($stocks as $s)
            <div class="col-6 col-md-4 kotak-produk"
              data-id="{{ $s->product_id }}"
              data-name="{{ $s->product->name }}"
              data-price="{{ $s->product->selling_price }}"
              data-stock="{{ $s->quantity }}"
              data-cat="{{ $s->product->category->name }}">
              <div class="card kartu-produk h-100 mb-0 border">
                <div class="card-body p-3">
                  <div class="fw-bold">{{ $s->product->name }}</div>
                  <div class="small text-muted">{{ $s->product->category->name }}</div>
                  <div class="small {{ $s->isLow() ? 'text-danger' : 'text-muted' }}">Sisa {{ $s->quantity }} {{ $s->product->unit }}</div>
                  <div class="d-flex justify-content-between align-items-center mt-2">
                    <span class="fw-bold text-primary">@rp($s->product->selling_price)</span>
                    <span class="badge badge-primary jml" style="display:none"></span>
                  </div>
                </div>
              </div>
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </div>

  {{-- keranjang --}}
  <div class="col-lg-4">
    <div class="card">
      <form action="{{ route('pos.store') }}" method="POST" id="pos-form">
        @csrf
        <input type="hidden" name="branch_id" value="{{ $branch_id }}">

        <div class="card-header">
          <div class="card-head-row">
            <div class="card-title">Pesanan</div>
            <small class="text-muted ms-auto">{{ now()->format('d/m/Y') }}</small>
          </div>
        </div>

        <div class="card-body">
          <div id="cart-items" class="mb-3"></div>

          <div class="d-flex justify-content-between align-items-center border-top pt-3 mb-3">
            <span class="fw-bold">Total</span>
            <span class="angka-besar" id="total-text">Rp 0</span>
          </div>

          <div class="mb-3 d-flex gap-3">
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method" id="bayar-cash" value="cash" {{ old('payment_method', 'cash') == 'cash' ? 'checked' : '' }}>
              <label class="form-check-label" for="bayar-cash">Tunai</label>
            </div>
            <div class="form-check">
              <input class="form-check-input" type="radio" name="payment_method" id="bayar-qris" value="qris" {{ old('payment_method') == 'qris' ? 'checked' : '' }}>
              <label class="form-check-label" for="bayar-qris">QRIS</label>
            </div>
          </div>

          <div id="paid-group" class="mb-3">
            <label class="form-label" for="paid-amount">Uang diterima</label>
            <input type="number" class="form-control" name="paid_amount" id="paid-amount" min="0" value="{{ old('paid_amount', 0) }}">
            <div class="d-flex flex-wrap gap-2 mt-2">
              <button type="button" class="btn btn-sm btn-outline-secondary" data-uang="pas">Uang pas</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" data-uang="20000">20.000</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" data-uang="50000">50.000</button>
              <button type="button" class="btn btn-sm btn-outline-secondary" data-uang="100000">100.000</button>
            </div>
          </div>

          <div class="d-flex justify-content-between mb-3" id="change-row">
            <span>Kembalian</span>
            <b id="change-text">Rp 0</b>
          </div>

          <div class="alert alert-info" id="qris-note" style="display:none">
            Kode QRIS berlaku {{ \App\Services\TransactionService::QRIS_MINUTES }} menit. Selama menunggu, stok barang ditahan untuk pesanan ini.
          </div>

          <button type="submit" class="btn btn-primary w-100" id="submit-btn" disabled>
            <i class="fas fa-check"></i> <span>Bayar tunai</span>
          </button>
          <p class="form-text text-center mb-0" id="submit-hint"></p>
        </div>
      </form>
    </div>
  </div>

</div>
@endsection

@push('scripts')
<script>
  window.POS_OLD_ITEMS = @json(old('items', []));
</script>
<script src="{{ asset('js/pos.js') }}"></script>
@endpush
