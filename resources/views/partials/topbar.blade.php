@php
  $me = auth()->user();
@endphp

<div class="main-header">
  <div class="main-header-logo">
    <div class="logo-header" data-background-color="dark">
      <a href="{{ route('dashboard') }}" class="logo">
        <img src="{{ asset('assets/img/brand/logo_light.svg') }}" alt="POS UMKM" class="navbar-brand" height="26" />
      </a>
      <div class="nav-toggle">
        <button class="btn btn-toggle toggle-sidebar"><i class="gg-menu-right"></i></button>
        <button class="btn btn-toggle sidenav-toggler"><i class="gg-menu-left"></i></button>
      </div>
      <button class="topbar-toggler more"><i class="gg-more-vertical-alt"></i></button>
    </div>
  </div>

  <nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
    <div class="container-fluid">
      <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">

        {{-- lonceng notifikasi stok menipis --}}
        <li class="nav-item topbar-icon dropdown hidden-caret">
          <a class="nav-link dropdown-toggle" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <i class="fa fa-bell"></i>
            @if ($bellUnread > 0)
              <span class="notification">{{ $bellUnread }}</span>
            @endif
          </a>
          <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">
            <li>
              <div class="dropdown-title d-flex justify-content-between align-items-center">
                Stok menipis
                @if ($bellUnread > 0)
                  <form action="{{ route('alerts.read-all') }}" method="POST" class="m-0">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0">Tandai dibaca</button>
                  </form>
                @endif
              </div>
            </li>
            <li>
              <div class="notif-scroll scrollbar-outer">
                <div class="notif-center">
                  @forelse ($bellAlerts as $a)
                    <a href="{{ route('alerts.index') }}">
                      <div class="notif-icon {{ $a->quantity == 0 ? 'notif-danger' : 'notif-warning' }}">
                        <i class="fa fa-box"></i>
                      </div>
                      <div class="notif-content">
                        <span class="block">{{ $a->product->name }}</span>
                        <span class="time">{{ $a->branch->name }}: {{ $a->quantity == 0 ? 'habis' : 'sisa ' . $a->quantity }} (minimum {{ $a->min_stock }})</span>
                      </div>
                    </a>
                  @empty
                    <div class="p-3 text-muted">Semua stok masih di atas batas minimum.</div>
                  @endforelse
                </div>
              </div>
            </li>
            @if ($bellTotal > 0)
              <li>
                <a class="see-all" href="{{ route('alerts.index') }}">Lihat semua ({{ $bellTotal }})<i class="fa fa-angle-right"></i></a>
              </li>
            @endif
          </ul>
        </li>

        <li class="nav-item topbar-user dropdown hidden-caret">
          <a class="dropdown-toggle profile-pic" data-bs-toggle="dropdown" href="#" aria-expanded="false">
            <div class="avatar-sm">
              <span class="avatar-title rounded-circle bg-primary text-white">{{ strtoupper(substr($me->name, 0, 1)) }}</span>
            </div>
            <span class="profile-username">
              <span class="op-7">Hai,</span>
              <span class="fw-bold">{{ $me->name }}</span>
            </span>
          </a>
          <ul class="dropdown-menu dropdown-user animated fadeIn">
            <div class="dropdown-user-scroll scrollbar-outer">
              <li>
                <div class="user-box">
                  <div class="u-text">
                    <h4>{{ $me->name }}</h4>
                    <p class="text-muted">{{ $me->email }}</p>
                    <p class="text-muted mb-0">
                      {{ $me->isOwner() ? 'Owner' : 'Kasir' }}@if ($me->branch) - {{ $me->branch->name }}@endif
                    </p>
                  </div>
                </div>
              </li>
              <li>
                <div class="dropdown-divider"></div>
                <form action="{{ route('logout') }}" method="POST">
                  @csrf
                  <button type="submit" class="dropdown-item">Keluar</button>
                </form>
              </li>
            </div>
          </ul>
        </li>
      </ul>
    </div>
  </nav>
</div>
