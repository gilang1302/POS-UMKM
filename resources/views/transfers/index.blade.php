@extends('layouts.app')

@section('title', 'Transfer stok')
@section('subtitle', 'Pindahkan barang antar cabang dan gudang pusat')

@section('actions')
  <a href="{{ route('stock-transfer.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Transfer baru</a>
@endsection

@section('content')
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>Kode</th>
          <th>Waktu</th>
          <th>Dari</th>
          <th>Ke</th>
          <th>Barang yang dipindahkan</th>
          <th>Oleh</th>
        </tr>
      </thead>
      <tbody>
        @forelse ($transfers as $t)
          <tr>
            <td>{{ $t->code }}</td>
            <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $t->fromBranch->name }}</td>
            <td>{{ $t->toBranch->name }}</td>
            <td>
              @foreach ($t->items as $item)
                <div>{{ $item->product->name }}: <b>{{ $item->qty }}</b></div>
              @endforeach
              @if ($t->note)
                <div class="small text-muted">Catatan: {{ $t->note }}</div>
              @endif
            </td>
            <td>{{ $t->user->name }}</td>
          </tr>
        @empty
          <tr><td colspan="6" class="text-center text-muted py-4">Belum ada transfer stok. Buat transfer baru untuk mengirim barang dari gudang ke cabang.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $transfers->links('partials.pagination') }}
</div>
@endsection
