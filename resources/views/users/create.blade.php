@extends('layouts.app')

@section('title', 'Tambah pengguna')

@section('content')
<div class="row">
  <div class="col-lg-7">
    <div class="card">
      <div class="card-body">
        <form action="{{ route('users.store') }}" method="POST">
          @csrf
          @include('users._form')
          <div class="mt-3 d-flex gap-2">
            <button type="submit" class="btn btn-primary">Simpan</button>
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">Batal</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection
