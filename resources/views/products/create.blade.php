@extends('layouts.app')

@section('title', 'Tambah Produk')

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('products.store') }}" method="POST">
      @csrf
      @include('products._form')
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>
@endsection
