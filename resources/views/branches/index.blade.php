@extends('layouts.app')

@section('title', 'Data Cabang')

@section('content')
<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">
    <h4 class="card-title mb-0">Daftar Cabang & Gudang</h4>
    <a href="{{ route('branches.create') }}" class="btn btn-primary btn-round">
      <i class="fa fa-plus"></i> Tambah Cabang
    </a>
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="branches-table" class="display table table-striped table-hover">
        <thead>
          <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama</th>
            <th>Tipe</th>
            <th>Alamat</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($branches as $b)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $b->code }}</td>
              <td>{{ $b->name }}</td>
              <td>
                @if ($b->type == 'warehouse')
                  <span class="badge badge-warning">Gudang</span>
                @else
                  <span class="badge badge-success">Cabang</span>
                @endif
              </td>
              <td>{{ $b->address }}</td>
              <td>
                <a href="{{ route('branches.edit', $b->id) }}" class="btn btn-sm btn-warning">Edit</a>
                <form action="{{ route('branches.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin hapus cabang ini?')">
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
    $('#branches-table').DataTable();
  });
</script>
@endpush
