@extends('layouts.app')

@section('title', 'Tambah Stok')

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('stock.store') }}" method="POST">
      @csrf
      <div class="form-group">
        <label>Cabang / Gudang</label>
        <select name="branch_id" class="form-select">
          @foreach ($branches as $b)
            <option value="{{ $b->id }}" {{ old('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Produk</label>
        <select name="product_id" class="form-select">
          @foreach ($products as $p)
            <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>{{ $p->code }} - {{ $p->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="form-group">
        <label>Jumlah Masuk</label>
        <input type="number" name="quantity" class="form-control" value="{{ old('quantity', 0) }}">
      </div>
      <div class="form-group">
        <label>Stok Minimum</label>
        <input type="number" name="min_stock" class="form-control" value="{{ old('min_stock', 5) }}">
      </div>
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="{{ route('stock.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>
@endsection
