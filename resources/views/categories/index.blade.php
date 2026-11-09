@extends('layouts.app')

@section('title', 'Kategori')
@section('subtitle', 'Pengelompokan produk di layar kasir')

@section('content')
<div class="row">
  <div class="col-lg-4">
  <div class="card">
    <div class="card-header"><div class="card-title">Kategori baru</div></div>
    <div class="card-body">
      <form action="{{ route('categories.store') }}" method="POST">
        @csrf
        <div class="mb-3">
          <label class="form-label" for="name">Nama kategori</label>
          <input class="form-control" type="text" name="name" id="name" required>
        </div>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </form>
    </div>
  </div>
  </div>

  <div class="col-lg-8">
  <div class="card">
    <div class="card-header"><div class="card-title">Daftar kategori</div></div>
    <div class="table-responsive">
      <table class="table table-hover">
        <thead>
          <tr><th>Nama</th><th class="text-end">Produk</th><th></th></tr>
        </thead>
        <tbody>
          @forelse ($categories as $c)
            <tr>
              <td>
                <form action="{{ route('categories.update', $c->id) }}" method="POST" class="d-flex gap-2">
                  @csrf
                  @method('PUT')
                  <input class="form-control" type="text" name="name" value="{{ $c->name }}" aria-label="Nama kategori">
                  <button type="submit" class="btn btn-outline-secondary btn-sm">Simpan</button>
                </form>
              </td>
              <td class="text-end">{{ $c->products_count }}</td>
              <td class="text-end">
                <form action="{{ route('categories.destroy', $c->id) }}" method="POST" data-konfirmasi="Hapus kategori {{ $c->name }}?">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</button>
                </form>
              </td>
            </tr>
          @empty
            <tr><td colspan="3" class="text-center text-muted py-4">Belum ada kategori.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
  </div>
</div>
@endsection
