@extends('layouts.app')

@section('title', 'Data Produk')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="card-title mb-0">Daftar Produk</h4>
    <a href="{{ route('products.create') }}" class="btn btn-primary btn-round">
      <i class="fa fa-plus"></i> Tambah Produk
    </a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="products-table" class="display table table-striped table-hover">
        <thead>
          <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama</th>
            <th>Kategori</th>
            <th>Harga Beli</th>
            <th>Harga Jual</th>
            <th>Satuan</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($products as $p)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $p->code }}</td>
              <td>{{ $p->name }}</td>
              <td>{{ $p->category->name }}</td>
              <td>Rp {{ number_format($p->cost_price, 0, ',', '.') }}</td>
              <td>Rp {{ number_format($p->selling_price, 0, ',', '.') }}</td>
              <td>{{ $p->unit }}</td>
              <td>
                <a href="{{ route('products.edit', $p->id) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('products.destroy', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus produk ini?')">
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
@endsection

@push('scripts')
<script>
  $(document).ready(function () {
    $('#products-table').DataTable();
  });
</script>
@endpush
