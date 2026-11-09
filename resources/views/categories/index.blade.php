@extends('layouts.app')

@section('title', 'Kategori Produk')

@section('content')
<div class="row">
  <div class="col-md-4">
    <div class="card">
      <div class="card-header"><h4 class="card-title">Tambah Kategori</h4></div>
      <div class="card-body">
        <form action="{{ route('categories.store') }}" method="POST">
          @csrf
          <div class="form-group">
            <label>Nama Kategori</label>
            <input type="text" name="name" class="form-control" required>
          </div>
          <button type="submit" class="btn btn-primary">Simpan</button>
        </form>
      </div>
    </div>
  </div>

  <div class="col-md-8">
    <div class="card">
      <div class="card-header"><h4 class="card-title">Daftar Kategori</h4></div>
      <div class="card-body">
        <table class="table table-striped">
          <thead>
            <tr>
              <th>No</th>
              <th>Nama</th>
              <th>Jumlah Produk</th>
              <th>Aksi</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($categories as $c)
              <tr>
                <td>{{ $loop->iteration }}</td>
                <td>
                  <form action="{{ route('categories.update', $c->id) }}" method="POST" class="d-flex gap-2">
                    @csrf
                    @method('PUT')
                    <input type="text" name="name" value="{{ $c->name }}" class="form-control form-control-sm">
                    <button type="submit" class="btn btn-sm btn-warning">Ubah</button>
                  </form>
                </td>
                <td>{{ $c->products_count }}</td>
                <td>
                  <form action="{{ route('categories.destroy', $c->id) }}" method="POST" onsubmit="return confirm('Hapus kategori?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                  </form>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>
@endsection
