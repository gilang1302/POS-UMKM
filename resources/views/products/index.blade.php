@extends('layouts.app')

@section('title', 'Produk')
@section('subtitle', 'Daftar barang yang dijual')

@section('actions')
  <a href="{{ route('products.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah produk</a>
@endsection

@section('content')
<div class="card">
  <div class="card-header">
    <div class="input-group" style="max-width: 320px">
      <span class="input-group-text"><i class="fas fa-search"></i></span>
      <input class="form-control" type="text" data-cari="#tabel-produk" placeholder="Cari produk" aria-label="Cari produk" autocomplete="off">
    </div>
  </div>
  <div class="table-responsive">
    <table class="table table-hover" id="tabel-produk">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Nama</th>
          <th>Kategori</th>
          <th class="text-end">Harga beli</th>
          <th class="text-end">Harga jual</th>
          <th>Satuan</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($products as $p)
          <tr>
            <td>{{ $p->code }}</td>
            <td><b>{{ $p->name }}</b></td>
            <td>{{ $p->category->name }}</td>
            <td class="text-end">@rp($p->cost_price)</td>
            <td class="text-end">@rp($p->selling_price)</td>
            <td>{{ $p->unit }}</td>
            <td class="text-end">
              <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('products.edit', $p->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-edit"></i> Ubah</a>
                <form action="{{ route('products.destroy', $p->id) }}" method="POST" data-konfirmasi="Hapus produk {{ $p->name }}?">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr class="baris-kosong"><td colspan="7" class="text-center text-muted py-4">Belum ada produk. Tambahkan produk pertama.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
