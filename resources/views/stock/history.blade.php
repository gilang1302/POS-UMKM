@extends('layouts.app')

@section('title', 'Kartu stok')
@section('subtitle', 'Setiap penambahan dan pengurangan stok tercatat di sini')

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('stock.history') }}" method="GET" class="row g-2 align-items-end">
      @if (auth()->user()->isOwner())
        <div class="col-md-3">
          <label class="form-label" for="branch_id">Lokasi</label>
          <select class="form-select" name="branch_id" id="branch_id">
            <option value="">Semua lokasi</option>
            @foreach ($branches as $b)
              <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
          </select>
        </div>
      @endif
      <div class="col-md-3">
        <label class="form-label" for="type">Jenis</label>
        <select class="form-select" name="type" id="type">
          <option value="">Semua jenis</option>
          @foreach (\App\Models\StockMovement::$typeLabels as $key => $label)
            <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      <div class="col-auto d-flex gap-2">
        <button type="submit" class="btn btn-primary">Tampilkan</button>
        <a href="{{ route('stock.history') }}" class="btn btn-outline-secondary">Atur ulang</a>
      </div>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Waktu</th>
          <th>Lokasi</th>
          <th>Produk</th>
          <th>Jenis</th>
          <th class="text-end">Perubahan</th>
          <th class="text-end">Stok akhir</th>
          <th>Referensi</th>
          <th>Oleh</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($movements as $m)
          <tr>
            <td>{{ $m->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $m->branch->name }}</td>
            <td>{{ $m->product->name }}</td>
            <td>{{ $m->typeLabel() }}</td>
            <td class="text-end {{ $m->qty_change < 0 ? 'text-danger' : '' }}">{{ $m->qty_change > 0 ? '+' : '' }}{{ $m->qty_change }}</td>
            <td class="text-end">{{ $m->qty_after }}</td>
            <td>{{ $m->reference ?: ($m->note ?: '-') }}</td>
            <td>{{ $m->user ? $m->user->name : '-' }}</td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center text-muted py-4">Belum ada pergerakan stok.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $movements->links('partials.pagination') }}
</div>
@endsection
