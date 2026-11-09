@extends('layouts.app')

@section('title', 'Dashboard')
@section('subtitle', auth()->user()->isOwner() ? 'Penjualan semua cabang hari ini' : 'Penjualan ' . auth()->user()->branch->name . ' hari ini')

@section('actions')
  <a href="{{ route('pos') }}" class="btn btn-primary"><i class="fas fa-shopping-cart"></i> Buka kasir</a>
@endsection

@section('content')
<div class="row">
  <div class="col-sm-6 col-md-3">
    <div class="card card-stats card-round">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-coins"></i></div></div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <p class="card-category">Omset hari ini</p>
              <h4 class="card-title" id="angka-omset">Rp 0</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="card card-stats card-round">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-receipt"></i></div></div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <p class="card-category">Transaksi lunas</p>
              <h4 class="card-title" id="angka-transaksi">0</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="card card-stats card-round">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon"><div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-calculator"></i></div></div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <p class="card-category">Rata-rata per transaksi</p>
              <h4 class="card-title" id="angka-rata">Rp 0</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="card card-stats card-round">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-qrcode"></i></div></div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <p class="card-category">QRIS menunggu bayar</p>
              <h4 class="card-title" id="angka-qris">0</h4>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-8">
    <div class="card card-round">
      <div class="card-header">
        <div class="card-head-row">
          <div class="card-title">Omset 7 hari terakhir</div>
          <small class="text-muted ms-auto">Diperbarui pukul <span id="waktu-update">-</span></small>
        </div>
      </div>
      <div class="card-body">
        <div id="grafik-wrap"><canvas id="grafik-omset"></canvas></div>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-round">
      <div class="card-header">
        <div class="card-head-row">
          <div class="card-title">Stok menipis (<span id="angka-alert">0</span>)</div>
          <a href="{{ route('alerts.index') }}" class="ms-auto small">Lihat semua</a>
        </div>
      </div>
      <div class="card-body">
        <div id="stok-menipis"></div>
      </div>
    </div>
  </div>
</div>

<div class="row">
  <div class="col-md-4">
    <div class="card card-round">
      <div class="card-header"><div class="card-title">{{ auth()->user()->isOwner() ? 'Omset per cabang' : 'Omset cabang' }}</div></div>
      <div class="card-body" id="omset-cabang"></div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-round">
      <div class="card-header"><div class="card-title">Produk terlaris hari ini</div></div>
      <div class="card-body" id="produk-terlaris"></div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-round">
      <div class="card-header">
        <div class="card-head-row">
          <div class="card-title">Transaksi terakhir</div>
          <a href="{{ route('transactions.index') }}" class="ms-auto small">Semua transaksi</a>
        </div>
      </div>
      <div class="card-body" id="transaksi-terakhir"></div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugin/chart.js/chart.min.js') }}"></script>
<script>
  window.DASH_DATA = @json($data);
  window.DASH_URL = "{{ route('dashboard.data') }}";
</script>
<script src="{{ asset('js/dashboard.js') }}"></script>
@endpush
