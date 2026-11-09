@extends('layouts.app')

@section('title', 'Stok menipis')
@section('subtitle', 'Peringatan muncul otomatis saat stok mencapai batas minimum')

@section('actions')
  @if ($open->whereNull('read_at')->count() > 0)
    <form action="{{ route('alerts.read-all') }}" method="POST" class="m-0">
      @csrf
      <button type="submit" class="btn btn-outline-secondary"><i class="fas fa-check"></i> Tandai semua dibaca</button>
    </form>
  @endif
@endsection

@section('content')
<div class="card">
  <div class="card-header"><div class="card-title">Perlu restock ({{ $open->count() }})</div></div>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Produk</th>
          <th>Lokasi</th>
          <th class="text-end">Stok</th>
          <th class="text-end">Minimum</th>
          <th>Sejak</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($open as $a)
          <tr>
            <td>
              <b>{{ $a->product->name }}</b>
              @if (!$a->read_at) <span class="badge badge-warning">Baru</span> @endif
            </td>
            <td>{{ $a->branch->name }}</td>
            <td class="text-end">
              @if ($a->quantity == 0)
                <span class="badge badge-danger">Habis</span>
              @else
                <span class="badge badge-warning">{{ $a->quantity }}</span>
              @endif
            </td>
            <td class="text-end">{{ $a->min_stock }}</td>
            <td>{{ $a->created_at->format('d/m/Y H:i') }}</td>
            <td class="text-end">
              <div class="d-flex gap-2 justify-content-end">
                @if (auth()->user()->isOwner())
                  <a href="{{ route('stock-transfer.create') }}" class="btn btn-outline-secondary btn-sm">Transfer</a>
                  <a href="{{ route('stock.create') }}" class="btn btn-outline-secondary btn-sm">Barang masuk</a>
                @endif
                @if (!$a->read_at)
                  <form action="{{ route('alerts.read', $a->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm">Tandai dibaca</button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-4">Tidak ada stok yang menipis. Semua barang masih di atas batas minimum.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>

@if ($resolved->count() > 0)
<div class="card">
  <div class="card-header"><div class="card-title">Sudah teratasi</div></div>
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr><th>Produk</th><th>Lokasi</th><th>Mulai menipis</th><th>Teratasi</th></tr>
      </thead>
      <tbody>
        @foreach ($resolved as $a)
          <tr>
            <td>{{ $a->product->name }}</td>
            <td>{{ $a->branch->name }}</td>
            <td>{{ $a->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $a->resolved_at->format('d/m/Y H:i') }}</td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endif
@endsection
