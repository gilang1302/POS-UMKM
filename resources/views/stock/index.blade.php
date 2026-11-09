@extends('layouts.app')

@section('title', 'Stok barang')
@section('subtitle', $branch ? $branch->name . ($branch->type == 'warehouse' ? ' (gudang)' : '') : '')

@section('actions')
  @if (auth()->user()->isOwner())
    <form action="{{ route('stock.index') }}" method="GET" class="m-0">
      <label class="visually-hidden" for="pilih-lokasi">Lokasi</label>
      <select class="form-select" name="branch_id" id="pilih-lokasi" onchange="this.form.submit()">
        @foreach ($branches as $b)
          <option value="{{ $b->id }}" {{ $branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
        @endforeach
      </select>
    </form>
    <a href="{{ route('stock.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Barang masuk</a>
  @endif
@endsection

@section('content')
@php
  $lowCount = $stocks->filter(function ($s) { return $s->isLow(); })->count();
@endphp

<div class="row">
  <div class="col-md-4">
    <div class="card card-stats card-round">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon"><div class="icon-big text-center icon-primary bubble-shadow-small"><i class="fas fa-box"></i></div></div>
          <div class="col col-stats ms-3 ms-sm-0"><div class="numbers"><p class="card-category">Jenis produk</p><h4 class="card-title">{{ $stocks->count() }}</h4></div></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-stats card-round">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon"><div class="icon-big text-center icon-info bubble-shadow-small"><i class="fas fa-boxes"></i></div></div>
          <div class="col col-stats ms-3 ms-sm-0"><div class="numbers"><p class="card-category">Total barang</p><h4 class="card-title">{{ $stocks->sum('quantity') }}</h4></div></div>
        </div>
      </div>
    </div>
  </div>
  <div class="col-md-4">
    <div class="card card-stats card-round">
      <div class="card-body">
        <div class="row align-items-center">
          <div class="col-icon"><div class="icon-big text-center icon-warning bubble-shadow-small"><i class="fas fa-exclamation-triangle"></i></div></div>
          <div class="col col-stats ms-3 ms-sm-0"><div class="numbers"><p class="card-category">Stok menipis atau habis</p><h4 class="card-title {{ $lowCount > 0 ? 'text-danger' : '' }}">{{ $lowCount }}</h4></div></div>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
  <div class="card-header">
    <div class="input-group" style="max-width: 320px">
      <span class="input-group-text"><i class="fas fa-search"></i></span>
      <input class="form-control" type="text" data-cari="#tabel-stok" placeholder="Cari produk" aria-label="Cari produk" autocomplete="off">
    </div>
  </div>

  <div class="table-responsive">
    <table class="table table-hover" id="tabel-stok">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Produk</th>
          <th>Kategori</th>
          <th class="text-end">Stok</th>
          <th class="text-end">Minimum</th>
          <th>Status</th>
          @if (auth()->user()->isOwner())
            <th></th>
          @endif
        </tr>
      </thead>
      <tbody>
        @forelse ($stocks as $s)
          <tr>
            <td>{{ $s->product->code }}</td>
            <td>{{ $s->product->name }}</td>
            <td>{{ $s->product->category->name }}</td>
            <td class="text-end"><b>{{ $s->quantity }}</b> {{ $s->product->unit }}</td>
            <td class="text-end">{{ $s->min_stock }}</td>
            <td>
              @if ($s->quantity == 0)
                <span class="badge badge-danger">Habis</span>
              @elseif ($s->isLow())
                <span class="badge badge-warning">Menipis</span>
              @else
                <span class="badge badge-success">Aman</span>
              @endif
            </td>
            @if (auth()->user()->isOwner())
              <td class="text-end"><a href="{{ route('stock.adjust', $s->id) }}" class="btn btn-outline-secondary btn-sm">Sesuaikan</a></td>
            @endif
          </tr>
        @empty
          <tr class="baris-kosong"><td colspan="7" class="text-center text-muted py-4">Belum ada stok di lokasi ini.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
