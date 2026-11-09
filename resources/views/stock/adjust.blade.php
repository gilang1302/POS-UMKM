@extends('layouts.app')

@section('title', 'Sesuaikan stok')
@section('subtitle', $stock->product->name . ' di ' . $stock->branch->name)

@section('content')
<div class="row">
  <div class="col-lg-7">
<div class="card">
  <div class="card-body">
    <p class="m-0">Stok tercatat di sistem: <b>{{ $stock->quantity }} {{ $stock->product->unit }}</b>. Isi jumlah hasil hitung di rak (stok opname), selisihnya dicatat di kartu stok.</p>

    <form action="{{ route('stock.adjust.update', $stock->id) }}" method="POST" class="mt-3">
      @csrf
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label" for="quantity">Stok hasil hitung</label>
          <input class="form-control" type="number" name="quantity" id="quantity" min="0" value="{{ old('quantity', $stock->quantity) }}">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label" for="min_stock">Stok minimum</label>
          <input class="form-control" type="number" name="min_stock" id="min_stock" min="0" value="{{ old('min_stock', $stock->min_stock) }}">
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="note">Alasan selisih (boleh kosong)</label>
        <input class="form-control" type="text" name="note" id="note" maxlength="200" value="{{ old('note') }}" placeholder="Misalnya: barang rusak, salah hitung">
      </div>
      <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Simpan penyesuaian</button>
        <a href="{{ route('stock.index', ['branch_id' => $stock->branch_id]) }}" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
  </div>
</div>
@endsection
