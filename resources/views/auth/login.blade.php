<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk - POS UMKM</title>
  <link rel="icon" href="{{ asset('assets/img/brand/favicon.svg') }}" type="image/svg+xml">
  <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/fonts.min.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/pos.min.css') }}">
  <link rel="stylesheet" href="{{ asset('css/tambahan.css') }}">
</head>
<body class="bg-light">
  <div class="container">
    <div class="row justify-content-center align-items-center" style="min-height: 100vh">
      <div class="col-md-6 col-lg-4">

        <div class="text-center mb-4">
          <img src="{{ asset('assets/img/brand/logo_dark.svg') }}" alt="POS UMKM" height="36">
          <p class="text-muted mt-2 mb-0">Kasir dan stok semua cabang dalam satu tempat</p>
        </div>

        <div class="card">
          <div class="card-body p-4">
            <h4 class="fw-bold mb-3">Masuk</h4>

            @if (session('error'))
              <div class="alert alert-danger" role="alert">{{ session('error') }}</div>
            @endif

            <form action="{{ route('login') }}" method="POST">
              @csrf
              <div class="mb-3">
                <label class="form-label" for="email">Email</label>
                <input type="email" class="form-control" name="email" id="email" value="{{ old('email') }}" required autofocus>
                @error('email') <div class="form-text text-danger">{{ $message }}</div> @enderror
              </div>
              <div class="mb-3">
                <label class="form-label" for="password">Kata sandi</label>
                <input type="password" class="form-control" name="password" id="password" required>
                @error('password') <div class="form-text text-danger">{{ $message }}</div> @enderror
              </div>
              <div class="form-check mb-3">
                <input type="checkbox" class="form-check-input" name="remember" id="remember">
                <label class="form-check-label" for="remember">Ingat saya di perangkat ini</label>
              </div>
              <button type="submit" class="btn btn-primary w-100">Masuk</button>
            </form>

            <hr>
            <div class="small text-muted">
              Akun contoh (kata sandi <code>password</code>):<br>
              <code>owner@umkm.test</code> untuk owner<br>
              <code>cashier1@umkm.test</code> untuk kasir Dago
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</body>
</html>
