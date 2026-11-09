@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="card">
  <div class="card-body">
    <div class="table-responsive">
      <table class="table table-striped table-hover">
        <thead>
          <tr>
            <th>No</th>
            <th>No Invoice</th>
            <th>Tanggal</th>
            <th>Cabang</th>
            <th>Kasir</th>
            <th>Metode</th>
            <th>Total</th>
            <th>Aksi</th>
          </tr>
        </thead>
        <tbody>
          @forelse ($transactions as $t)
            <tr>
              <td>{{ $transactions->firstItem() + $loop->index }}</td>
              <td>{{ $t->invoice_no }}</td>
              <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
              <td>{{ $t->branch->name }}</td>
              <td>{{ $t->user->name }}</td>
              <td>{{ strtoupper($t->payment_method) }}</td>
              <td>Rp {{ number_format($t->total, 0, ',', '.') }}</td>
              <td><a href="{{ route('transactions.show', $t->id) }}" class="btn btn-sm btn-info">Detail</a></td>
            </tr>
          @empty
            <tr><td colspan="8" class="text-center text-muted">Belum ada transaksi</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    {{ $transactions->links() }}
  </div>
</div>
@endsection
