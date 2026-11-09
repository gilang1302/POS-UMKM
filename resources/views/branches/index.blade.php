@extends('layouts.app')

@section('title', 'Cabang & gudang')
@section('subtitle', 'Lokasi tempat penjualan dan penyimpanan stok')

@section('actions')
  <a href="{{ route('branches.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah lokasi</a>
@endsection

@section('content')
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr><th>Kode</th><th>Nama</th><th>Tipe</th><th>Alamat</th><th>Telepon</th><th></th></tr>
      </thead>
      <tbody>
        @forelse ($branches as $b)
          <tr>
            <td>{{ $b->code }}</td>
            <td><b>{{ $b->name }}</b></td>
            <td>
              @if ($b->type == 'warehouse')
                <span class="badge badge-warning">Gudang</span>
              @else
                <span class="badge badge-success">Cabang</span>
              @endif
            </td>
            <td>{{ $b->address ?: '-' }}</td>
            <td>{{ $b->phone ?: '-' }}</td>
            <td class="text-end">
              <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('branches.edit', $b->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-edit"></i> Ubah</a>
                <form action="{{ route('branches.destroy', $b->id) }}" method="POST" data-konfirmasi="Hapus {{ $b->name }}?">
                  @csrf
                  @method('DELETE')
                  <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</button>
                </form>
              </div>
            </td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-4">Belum ada cabang. Tambahkan cabang pertama.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
</div>
@endsection
