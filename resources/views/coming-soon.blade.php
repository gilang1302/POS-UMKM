@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="card">
  <div class="card-body text-center py-5">
    <i class="fas fa-tools fa-3x text-muted mb-3"></i>
    <h4>Fitur ini belum dikerjakan</h4>
    <p class="text-muted">Halaman "{{ $title }}" masih dalam tahap pengembangan.</p>
    <a href="{{ route('dashboard') }}" class="btn btn-primary">Kembali ke Dashboard</a>
  </div>
</div>
@endsection
