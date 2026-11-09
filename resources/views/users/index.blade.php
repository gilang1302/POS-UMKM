@extends('layouts.app')

@section('title', 'Pengguna')
@section('subtitle', 'Akun owner dan kasir')

@section('actions')
  <a href="{{ route('users.create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah pengguna</a>
@endsection

@section('content')
<div class="card">
  <div class="table-responsive">
    <table class="table table-hover">
      <thead>
        <tr><th>Nama</th><th>Email</th><th>Peran</th><th>Cabang</th><th></th></tr>
      </thead>
      <tbody>
        @foreach ($users as $u)
          <tr>
            <td>{{ $u->name }} @if ($u->id == auth()->id()) <span class="small text-muted">(Anda)</span> @endif</td>
            <td>{{ $u->email }}</td>
            <td>{{ $u->isOwner() ? 'Owner' : 'Kasir' }}</td>
            <td>{{ $u->branch ? $u->branch->name : 'Semua cabang' }}</td>
            <td class="text-end">
              <div class="d-flex gap-2 justify-content-end">
                <a href="{{ route('users.edit', $u->id) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-edit"></i> Ubah</a>
                @if ($u->id != auth()->id())
                  <form action="{{ route('users.destroy', $u->id) }}" method="POST" data-konfirmasi="Hapus pengguna {{ $u->name }}?">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i> Hapus</button>
                  </form>
                @endif
              </div>
            </td>
          </tr>
        @endforeach
      </tbody>
    </table>
  </div>
</div>
@endsection
