@extends('layouts.app')

@section('title', 'Stok Barang')

@section('content')
<div class="card">
  <div class="card-header">
    <div class="d-flex justify-content-between align-items-center">
      <h4 class="card-title mb-0">Stok per Lokasi</h4>
      @if (auth()->user()->isOwner())
        <a href="{{ route('stock.create') }}" class="btn btn-primary btn-round">
          <i class="fa fa-plus"></i> Tambah Stok
        </a>
      @endif
    </div>

    @if (auth()->user()->isOwner())
      <form action="{{ route('stock.index') }}" method="GET" class="row mt-3">
        <div class="col-md-4">
          <select name="branch_id" class="form-select" onchange="this.form.submit()">
            @foreach ($branches as $b)
              <option value="{{ $b->id }}" {{ $branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
          </select>
        </div>
      </form>
    @endif
  </div>
  <div class="card-body">
    <div class="table-responsive">
      <table id="stock-table" class="display table table-striped table-hover">
        <thead>
          <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Produk</th>
            <th>Kategori</th>
            <th>Stok</th>
            <th>Minimum</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          @foreach ($stocks as $s)
            <tr>
              <td>{{ $loop->iteration }}</td>
              <td>{{ $s->product->code }}</td>
              <td>{{ $s->product->name }}</td>
              <td>{{ $s->product->category->name }}</td>
              <td>{{ $s->quantity }} {{ $s->product->unit }}</td>
              <td>{{ $s->min_stock }}</td>
              <td>
                @if ($s->quantity <= $s->min_stock)
                  <span class="badge badge-danger">Menipis</span>
                @else
                  <span class="badge badge-success">Aman</span>
                @endif
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
    $('#stock-table').DataTable();
  });
</script>
@endpush
