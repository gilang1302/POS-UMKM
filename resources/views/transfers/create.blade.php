@extends('layouts.app')

@section('title', 'Transfer baru')
@section('subtitle', 'Stok lokasi asal berkurang dan stok lokasi tujuan bertambah sekaligus')

@section('content')
<form action="{{ route('stock-transfer.store') }}" method="POST" id="transfer-form">
  @csrf

  <div class="card">
    <div class="card-body">
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label" for="from_branch_id">Dari</label>
          <select class="form-select" name="from_branch_id" id="from_branch_id">
            @foreach ($branches as $b)
              <option value="{{ $b->id }}" {{ old('from_branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
          </select>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label" for="to_branch_id">Ke</label>
          <select class="form-select" name="to_branch_id" id="to_branch_id">
            @foreach ($branches as $b)
              <option value="{{ $b->id }}" {{ old('to_branch_id', $branches->count() > 1 ? $branches[1]->id : '') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
          </select>
        </div>
      </div>
      <div>
        <label class="form-label" for="note">Catatan (boleh kosong)</label>
        <input type="text" class="form-control" name="note" id="note" maxlength="200" value="{{ old('note') }}" placeholder="Misalnya: restock mingguan">
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-header">
      <div class="card-head-row">
        <div class="card-title">Barang yang dipindahkan</div>
        <button type="button" class="btn btn-outline-secondary btn-sm ms-auto" id="add-item"><i class="fas fa-plus"></i> Tambah produk</button>
      </div>
    </div>
    <div class="table-responsive">
      <table class="table">
        <thead>
          <tr><th>Produk</th><th>Jumlah</th><th></th><th></th></tr>
        </thead>
        <tbody id="item-body"></tbody>
      </table>
    </div>
    <div class="text-center text-muted p-4" id="item-kosong">Belum ada produk. Tekan "Tambah produk" untuk memilih barang dari lokasi asal.</div>
  </div>

  <p class="text-danger" id="transfer-error" role="alert"></p>

  <div class="d-flex gap-2">
    <button type="submit" class="btn btn-primary"><i class="fas fa-exchange-alt"></i> Proses transfer</button>
    <a href="{{ route('stock-transfer.index') }}" class="btn btn-outline-secondary">Batal</a>
  </div>
</form>
@endsection

@push('scripts')
<script>
  window.TRANSFER_STOCK = @json($stockMap);
  window.TRANSFER_OLD = @json(old('items', []));
</script>
<script src="{{ asset('js/transfer.js') }}"></script>
@endpush
