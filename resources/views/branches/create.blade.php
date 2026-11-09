@extends('layouts.app')

@section('title', 'Tambah Cabang')

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('branches.store') }}" method="POST">
      @csrf
      @include('branches._form')
      <button type="submit" class="btn btn-primary">Simpan</button>
      <a href="{{ route('branches.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>
@endsection
