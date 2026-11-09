@extends('layouts.app')

@section('title', 'Edit Produk')

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('products.update', $product->id) }}" method="POST">
      @csrf
      @method('PUT')
      @include('products._form')
      <button type="submit" class="btn btn-primary">Update</button>
      <a href="{{ route('products.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>
@endsection
