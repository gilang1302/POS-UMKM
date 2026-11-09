@extends('layouts.app')

@section('title', 'Laporan keuangan')
@section('subtitle', $report['title'] . ': ' . $report['period'] . ', ' . $report['branch'])

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('reports.index') }}" method="GET" class="row g-3 align-items-end" id="form-laporan">
      <div class="col-md-3">
        <span class="form-label d-block">Jenis laporan</span>
        <div>
          <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="type" id="jenis-harian" value="daily" {{ $type == 'daily' ? 'checked' : '' }}><label class="form-check-label" for="jenis-harian">Harian</label></div>
          <div class="form-check form-check-inline"><input class="form-check-input" type="radio" name="type" id="jenis-bulanan" value="monthly" {{ $type == 'monthly' ? 'checked' : '' }}><label class="form-check-label" for="jenis-bulanan">Bulanan</label></div>
        </div>
      </div>
      <div class="col-md-3" id="grup-tanggal">
        <label class="form-label" for="date">Tanggal</label>
        <input type="date" class="form-control" name="date" id="date" value="{{ $date }}">
      </div>
      <div class="col-md-3" id="grup-bulan">
        <label class="form-label" for="month">Bulan</label>
        <input type="month" class="form-control" name="month" id="month" value="{{ $month }}">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="branch_id">Cabang</label>
        <select class="form-select" name="branch_id" id="branch_id">
          <option value="">Semua cabang</option>
          @foreach ($branches as $b)
            <option value="{{ $b->id }}" {{ $branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-12 d-flex flex-wrap gap-2">
        <button type="submit" class="btn btn-primary">Tampilkan</button>
        <button type="submit" form="form-laporan" formaction="{{ route('reports.pdf') }}" class="btn btn-outline-secondary"><i class="fas fa-file-pdf"></i> Unduh PDF</button>
        <button type="submit" form="form-laporan" formaction="{{ route('reports.excel') }}" class="btn btn-outline-secondary"><i class="fas fa-file-excel"></i> Unduh Excel</button>
      </div>
    </form>
  </div>
</div>

<div class="row">
  <div class="col-sm-6 col-md-3">
    <div class="card card-stats card-round">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-receipt"></i></div></div>
          <div class="col col-stats ms-3 ms-sm-0"><div class="numbers"><p class="card-category">Transaksi lunas</p><h4 class="card-title">{{ $report['summary']['count'] }}</h4></div></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="card card-stats card-round">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon"><div class="icon-big text-center icon-success bubble-shadow-small"><i class="fas fa-coins"></i></div></div>
          <div class="col col-stats ms-3 ms-sm-0"><div class="numbers"><p class="card-category">Omset</p><h4 class="card-title">@rp($report['summary']['total'])</h4></div></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="card card-stats card-round">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon"><div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-box"></i></div></div>
          <div class="col col-stats ms-3 ms-sm-0"><div class="numbers"><p class="card-category">Harga pokok barang terjual</p><h4 class="card-title">@rp($report['summary']['cogs'])</h4></div></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-sm-6 col-md-3">
    <div class="card card-stats card-round">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-chart-line"></i></div></div>
          <div class="col col-stats ms-3 ms-sm-0"><div class="numbers"><p class="card-category">Laba kotor</p><h4 class="card-title">@rp($report['summary']['profit'])</h4></div></div>
        </div>
      </div>
    </div>
  </div>
</div>


<div class="row">
  <div class="col-md-6">
  <div class="card">
    <div class="card-header"><div class="card-title">Metode pembayaran</div></div>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead><tr><th>Metode</th><th class="text-end">Transaksi</th><th class="text-end">Jumlah</th></tr></thead>
        <tbody>
          <tr><td>Tunai</td><td class="text-end">{{ $report['summary']['cash_count'] }}</td><td class="text-end">@rp($report['summary']['cash_total'])</td></tr>
          <tr><td>QRIS</td><td class="text-end">{{ $report['summary']['qris_count'] }}</td><td class="text-end">@rp($report['summary']['qris_total'])</td></tr>
        </tbody>
      </table>
    </div>
  </div>
  </div>

  <div class="col-md-6">
  <div class="card">
    <div class="card-header"><div class="card-title">{{ $type == 'monthly' ? 'Omset per hari' : 'Omset per cabang' }}</div></div>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr><th>{{ $type == 'monthly' ? 'Tanggal' : 'Cabang' }}</th><th class="text-end">Transaksi</th><th class="text-end">Omset</th></tr>
        </thead>
        <tbody>
          @php
            $rows = $type == 'monthly' ? $report['per_day'] : $report['per_branch'];
          @endphp
          @forelse ($rows as $r)
            <tr>
              <td>{{ $type == 'monthly' ? $r['date'] : $r['name'] }}</td>
              <td class="text-end">{{ $r['count'] }}</td>
              <td class="text-end">@rp($r['total'])</td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-center text-muted py-4">Belum ada penjualan pada periode ini.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  </div>
</div>

<div class="card">
  <div class="card-header"><div class="card-title">Produk terjual</div></div>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr><th>Kode</th><th>Produk</th><th class="text-end">Terjual</th><th class="text-end">Pendapatan</th></tr>
      </thead>
      <tbody>
        @forelse ($report['products'] as $p)
          <tr>
            <td>{{ $p['code'] }}</td>
            <td>{{ $p['name'] }}</td>
            <td class="text-end">{{ $p['qty'] }}</td>
            <td class="text-end">@rp($p['revenue'])</td>
          </tr>
        @empty
          <tr><td colspan="4" class="text-center text-muted py-4">Belum ada produk terjual pada periode ini.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

<div class="card">
  <div class="card-header"><div class="card-title">Daftar transaksi ({{ $report['summary']['count'] }})</div></div>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr><th>Invoice</th><th>Waktu</th><th>Cabang</th><th>Kasir</th><th>Metode</th><th class="text-end">Total</th></tr>
      </thead>
      <tbody>
        @forelse ($report['transactions'] as $t)
          <tr>
            <td>{{ $t['invoice'] }}</td>
            <td>{{ $t['time'] }}</td>
            <td>{{ $t['branch'] }}</td>
            <td>{{ $t['cashier'] }}</td>
            <td>{{ $t['method'] }}</td>
            <td class="text-end">@rp($t['total'])</td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada transaksi lunas pada periode ini.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection

@push('scripts')
<script>
  // tampilkan kolom tanggal atau bulan sesuai jenis laporan
  function aturKolomPeriode() {
    var bulanan = document.querySelector('input[name="type"]:checked').value === 'monthly';
    document.getElementById('grup-tanggal').style.display = bulanan ? 'none' : '';
    document.getElementById('grup-bulan').style.display = bulanan ? '' : 'none';
  }

  document.querySelectorAll('input[name="type"]').forEach(function (r) {
    r.addEventListener('change', aturKolomPeriode);
  });
  aturKolomPeriode();
</script>
@endpush
