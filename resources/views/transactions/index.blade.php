@extends('layouts.app')

@section('title', 'Riwayat transaksi')
@section('subtitle', auth()->user()->isOwner() ? 'Semua cabang' : 'Cabang ' . auth()->user()->branch->name)

@section('content')
<div class="card">
  <div class="card-body">
    <form action="{{ route('transactions.index') }}" method="GET" class="row g-2 align-items-end">
      <div class="col-md-3">
        <label class="form-label" for="q">No invoice</label>
        <input class="form-control" type="text" name="q" id="q" value="{{ request('q') }}" placeholder="INV-...">
      </div>
      <div class="col-md-3">
        <label class="form-label" for="status">Status</label>
        <select class="form-select" name="status" id="status">
          <option value="">Semua</option>
          @foreach (\App\Models\Transaction::$statusLabels as $key => $label)
            <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
          @endforeach
        </select>
      </div>
      @if (auth()->user()->isOwner())
        <div class="col-md-3">
          <label class="form-label" for="branch_id">Cabang</label>
          <select class="form-select" name="branch_id" id="branch_id">
            <option value="">Semua cabang</option>
            @foreach ($branches as $b)
              <option value="{{ $b->id }}" {{ request('branch_id') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
            @endforeach
          </select>
        </div>
      @endif
      <div class="col-auto d-flex gap-2">
        <button type="submit" class="btn btn-primary">Cari</button>
        <a href="{{ route('transactions.index') }}" class="btn btn-outline-secondary">Atur ulang</a>
      </div>
    </form>
  </div>

  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr>
          <th>No invoice</th>
          <th>Waktu</th>
          <th>Cabang</th>
          <th>Kasir</th>
          <th>Metode</th>
          <th>Status</th>
          <th class="text-end">Total</th>
          <th></th>
        </tr>
      </thead>
      <tbody>
        @forelse ($transactions as $t)
          <tr>
            <td><a href="{{ route('transactions.show', $t->id) }}">{{ $t->invoice_no }}</a></td>
            <td>{{ $t->created_at->format('d/m/Y H:i') }}</td>
            <td>{{ $t->branch->name }}</td>
            <td>{{ $t->user->name }}</td>
            <td>{{ strtoupper($t->payment_method) }}</td>
            <td><span class="badge {{ $t->badgeClass() }}">{{ $t->statusLabel() }}</span></td>
            <td class="text-end">@rp($t->total)</td>
            <td class="text-end"><a href="{{ route('transactions.show', $t->id) }}" class="btn btn-outline-secondary btn-sm">Detail</a></td>
          </tr>
        @empty
          <tr><td colspan="8" class="text-center text-muted py-4">Belum ada transaksi yang cocok dengan pencarian ini.</td></tr>
        @endforelse
      </tbody>
    </table>
  </div>
  {{ $transactions->links('partials.pagination') }}
</div>
@endsection
