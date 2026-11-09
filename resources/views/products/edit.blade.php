@extends('layouts.app')

@section('title', 'Ubah produk')
@section('subtitle', $product->name)

@section('content')
<div class="row">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('products.update', $product->id) }}" method="POST">
          @csrf
          @method('PUT')
          @include('products._form')
          <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan perubahan</button>
            <a href="{{ route('products.index') }}" class="btn btn-outline-secondary">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
