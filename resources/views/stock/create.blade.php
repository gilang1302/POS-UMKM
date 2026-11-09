@extends('layouts.app')

@section('title', 'Barang masuk')
@section('subtitle', 'Tambah stok produk di cabang atau gudang')

@section('content')
<div class="row">
  <div class="col-lg-7">
<div class="card">
  <div class="card-body">
    <form action="{{ route('stock.store') }}" method="POST">
      @csrf
      <div class="mb-3">
        <label class="form-label" for="branch_id">Cabang / gudang</label>
        <select class="form-select" name="branch_id" id="branch_id">
          @foreach ($branches as $b)
            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="mb-3">
        <label class="form-label" for="product_id">Produk</label>
        <select class="form-select" name="product_id" id="product_id">
          @foreach ($products as $p)
            <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->code }} - {{ $p->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label" for="quantity">Jumlah masuk</label>
          <input class="form-control" type="number" name="quantity" id="quantity" min="0" value="{{ old('quantity', 0) }}">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label" for="min_stock">Stok minimum</label>
          <input class="form-control" type="number" name="min_stock" id="min_stock" min="0" value="{{ old('min_stock', 5) }}">
          <div class="form-text">Notifikasi muncul saat stok sama dengan atau di bawah angka ini.</div>
        </div>
      </div>
      <div class="mb-3">
        <label class="form-label" for="note">Catatan (boleh kosong)</label>
        <input class="form-control" type="text" name="note" id="note" maxlength="200" value="{{ old('note') }}" placeholder="Misalnya: kiriman supplier tanggal 9">
      </div>
      <div class="mt-3 d-flex gap-2">
        <button type="submit" class="btn btn-primary">Simpan stok</button>
        <a href="{{ route('stock.index') }}" class="btn btn-outline-secondary">Batal</a>
      </div>
    </form>
  </div>
</div>
  </div>
</div>
@endsection
