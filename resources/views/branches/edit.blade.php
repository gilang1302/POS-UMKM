@extends('layouts.app')

@section('title', 'Edit Cabang')

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('branches.update', $branch->id) }}" method="POST">
      @csrf
      @method('PUT')
      @include('branches._form')
      <button type="submit" class="btn btn-primary">Update</button>
      <a href="{{ route('branches.index') }}" class="btn btn-secondary">Batal</a>
    </form>
  </div>
</div>
@endsection
