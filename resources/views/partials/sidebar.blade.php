@php
  $isOwner = auth()->user()->isOwner();
@endphp

<div class="sidebar" data-background-color="dark">
  <div class="sidebar-logo">
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

  <div class="sidebar-wrapper scrollbar scrollbar-inner">
    <div class="sidebar-content">
      <ul class="nav nav-secondary">

        <li class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
          <a href="{{ route('dashboard') }}">
            <i class="fas fa-home"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <li class="nav-section">
          <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
          <h4 class="text-section">Penjualan</h4>
        </li>
        <li class="nav-item {{ request()->routeIs('pos') ? 'active' : '' }}">
          <a href="{{ route('pos') }}">
            <i class="fas fa-shopping-cart"></i>
            <p>Kasir (POS)</p>
          </a>
        </li>
        <li class="nav-item {{ request()->routeIs('transactions.*', 'qris.*') ? 'active' : '' }}">
          <a href="{{ route('transactions.index') }}">
            <i class="fas fa-receipt"></i>
            <p>Riwayat Transaksi</p>
          </a>
        </li>

        <li class="nav-section">
          <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
          <h4 class="text-section">Inventaris</h4>
        </li>
        <li class="nav-item {{ request()->routeIs('stock.index', 'stock.create', 'stock.adjust') ? 'active' : '' }}">
          <a href="{{ route('stock.index') }}">
            <i class="fas fa-boxes"></i>
            <p>Stok Barang</p>
          </a>
        </li>
        <li class="nav-item {{ request()->routeIs('stock.history') ? 'active' : '' }}">
          <a href="{{ route('stock.history') }}">
            <i class="fas fa-clipboard-list"></i>
            <p>Kartu Stok</p>
          </a>
        </li>
        <li class="nav-item {{ request()->routeIs('alerts.*') ? 'active' : '' }}">
          <a href="{{ route('alerts.index') }}">
            <i class="fas fa-exclamation-triangle"></i>
            <p>Stok Menipis</p>
          </a>
        </li>

        @if ($isOwner)
          <li class="nav-item {{ request()->routeIs('stock-transfer.*') ? 'active' : '' }}">
            <a href="{{ route('stock-transfer.index') }}">
              <i class="fas fa-exchange-alt"></i>
              <p>Transfer Antar Cabang</p>
            </a>
          </li>

          <li class="nav-section">
            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
            <h4 class="text-section">Data Master</h4>
          </li>
          <li class="nav-item {{ request()->routeIs('products.*') ? 'active' : '' }}">
            <a href="{{ route('products.index') }}">
              <i class="fas fa-box"></i>
              <p>Produk</p>
            </a>
          </li>
          <li class="nav-item {{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <a href="{{ route('categories.index') }}">
              <i class="fas fa-tags"></i>
              <p>Kategori</p>
            </a>
          </li>
          <li class="nav-item {{ request()->routeIs('branches.*') ? 'active' : '' }}">
            <a href="{{ route('branches.index') }}">
              <i class="fas fa-store"></i>
              <p>Cabang &amp; Gudang</p>
            </a>
          </li>
          <li class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <a href="{{ route('users.index') }}">
              <i class="fas fa-users"></i>
              <p>Pengguna</p>
            </a>
          </li>

          <li class="nav-section">
            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
            <h4 class="text-section">Laporan</h4>
          </li>
          <li class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <a href="{{ route('reports.index') }}">
              <i class="fas fa-file-invoice-dollar"></i>
              <p>Laporan Penjualan</p>
            </a>
          </li>
        @endif

      </ul>
    </div>
  </div>
</div>
