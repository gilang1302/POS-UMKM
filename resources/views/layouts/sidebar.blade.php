<div class="sidebar" data-background-color="dark">
  <div class="sidebar-logo">
    <div class="logo-header" data-background-color="dark">
      <a href="{{ route('dashboard') }}" class="logo">
        <img src="{{ asset('assets/img/kaiadmin/logo_light.svg') }}" alt="navbar brand" class="navbar-brand" height="20" />
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

        <li class="nav-item {{ request()->is('dashboard') ? 'active' : '' }}">
          <a href="{{ route('dashboard') }}">
            <i class="fas fa-home"></i>
            <p>Dashboard</p>
          </a>
        </li>

        <li class="nav-section">
          <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
          <h4 class="text-section">Transaksi</h4>
        </li>
        <li class="nav-item {{ request()->is('pos') ? 'active' : '' }}">
          <a href="{{ route('pos') }}">
            <i class="fas fa-cash-register"></i>
            <p>Kasir (POS)</p>
          </a>
        </li>
        <li class="nav-item {{ request()->is('transactions*') ? 'active' : '' }}">
          <a href="{{ route('transactions.index') }}">
            <i class="fas fa-receipt"></i>
            <p>Riwayat Transaksi</p>
          </a>
        </li>

        <li class="nav-section">
          <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
          <h4 class="text-section">Inventaris</h4>
        </li>
        <li class="nav-item {{ request()->is('stock*') ? 'active' : '' }}">
          <a href="{{ route('stock.index') }}">
            <i class="fas fa-boxes"></i>
            <p>Stok Barang</p>
          </a>
        </li>

        @if (auth()->user()->isOwner())
          <li class="nav-item {{ request()->is('stock-transfer*') ? 'active' : '' }}">
            <a href="{{ route('stock-transfer.index') }}">
              <i class="fas fa-exchange-alt"></i>
              <p>Transfer Stok</p>
              <span class="badge badge-secondary">soon</span>
            </a>
          </li>
          <li class="nav-item {{ request()->is('products*') ? 'active' : '' }}">
            <a href="{{ route('products.index') }}">
              <i class="fas fa-box"></i>
              <p>Data Produk</p>
            </a>
          </li>
          <li class="nav-item {{ request()->is('categories*') ? 'active' : '' }}">
            <a href="{{ route('categories.index') }}">
              <i class="fas fa-tags"></i>
              <p>Kategori</p>
            </a>
          </li>

          <li class="nav-section">
            <span class="sidebar-mini-icon"><i class="fa fa-ellipsis-h"></i></span>
            <h4 class="text-section">Owner</h4>
          </li>
          <li class="nav-item {{ request()->is('branches*') ? 'active' : '' }}">
            <a href="{{ route('branches.index') }}">
              <i class="fas fa-store"></i>
              <p>Data Cabang</p>
            </a>
          </li>
          <li class="nav-item {{ request()->is('reports*') ? 'active' : '' }}">
            <a href="{{ route('reports.index') }}">
              <i class="fas fa-file-invoice-dollar"></i>
              <p>Laporan</p>
              <span class="badge badge-secondary">soon</span>
            </a>
          </li>
        @endif

      </ul>
    </div>
  </div>
</div>
