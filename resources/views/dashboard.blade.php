@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="row">
  <div class="col-sm-6 col-md-3">
    <div class="card card-stats card-round">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon">
            <div class="icon-big text-center icon-success bubble-shadow-small">
              <i class="fas fa-money-bill-wave"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <p class="card-category">Omset Hari Ini</p>
              <h4 class="card-title">Rp {{ number_format($today_revenue, 0, ',', '.') }}</h4>
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
          <div class="col-icon">
            <div class="icon-big text-center icon-primary bubble-shadow-small">
              <i class="fas fa-receipt"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <p class="card-category">Transaksi Hari Ini</p>
              <h4 class="card-title">{{ $today_transactions }}</h4>
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
          <div class="col-icon">
            <div class="icon-big text-center icon-warning bubble-shadow-small">
              <i class="fas fa-exclamation-triangle"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <p class="card-category">Stok Menipis</p>
              <h4 class="card-title">{{ $low_stock_count }}</h4>
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
          <div class="col-icon">
            <div class="icon-big text-center icon-info bubble-shadow-small">
              <i class="fas fa-store"></i>
            </div>
          </div>
          <div class="col col-stats ms-3 ms-sm-0">
            <div class="numbers">
              <p class="card-category">Cabang / Produk</p>
              <h4 class="card-title">{{ $total_branches }} / {{ $total_products }}</h4>
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
        <div class="card-title">Grafik Omset 7 Hari Terakhir</div>
      </div>
      <div class="card-body">
        {{-- TODO: grafik ini masih data dummy, nanti ambil dari tabel transaksis --}}
        <div style="height: 260px;">
          <canvas id="chartOmset"></canvas>
        </div>
        <small class="text-muted">*data sementara (dummy)</small>
      </div>
    </div>
  </div>

  <div class="col-md-4">
    <div class="card card-round">
      <div class="card-header">
        <div class="card-title">Transaksi Terakhir</div>
      </div>
      <div class="card-body">
        @forelse ($latest_transactions as $t)
          <div class="d-flex justify-content-between border-bottom py-2">
            <div>
              <a href="{{ route('transactions.show', $t->id) }}">{{ $t->invoice_no }}</a><br>
              <small class="text-muted">{{ $t->branch->name }}</small>
            </div>
            <div class="fw-bold">Rp {{ number_format($t->total, 0, ',', '.') }}</div>
          </div>
        @empty
          <p class="text-muted mb-0">Belum ada transaksi.</p>
        @endforelse
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('assets/js/plugin/chart.js/chart.min.js') }}"></script>
<script>
  // data dummy dulu
  var ctx = document.getElementById('chartOmset').getContext('2d');
  new Chart(ctx, {
    type: 'line',
    data: {
      labels: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
      datasets: [{
        label: 'Omset (Rp)',
        data: [450000, 520000, 380000, 610000, 720000, 980000, 850000],
        borderColor: '#1d7af3',
        backgroundColor: 'rgba(29,122,243,0.1)',
        fill: true
      }]
    },
    options: { responsive: true, maintainAspectRatio: false }
  });
</script>
@endpush
