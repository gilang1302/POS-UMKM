<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Login - POS UMKM</title>
    <link rel="icon" href="{{ asset('assets/img/kaiadmin/favicon.ico') }}" type="image/x-icon" />
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}" />
    <link rel="stylesheet" href="{{ asset('assets/css/kaiadmin.min.css') }}" />
  </head>
  <body class="bg-light">
    <div class="container" style="max-width: 420px; margin-top: 90px;">
      <div class="card">
        <div class="card-body p-4">
          <div class="text-center mb-4">
            <img src="{{ asset('assets/img/kaiadmin/logo_dark.svg') }}" height="26" alt="logo" />
            <h4 class="mt-3 fw-bold">Login</h4>
            <p class="text-muted">Sistem POS &amp; Inventaris Multi-Cabang</p>
          </div>

          @if (session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
          @endif

          <form action="/login" method="POST">
            @csrf
            <div class="form-group">
              <label for="email">Email</label>
              <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus />
              @error('email') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="form-group">
              <label for="password">Password</label>
              <input type="password" name="password" id="password" class="form-control" required />
              @error('password') <small class="text-danger">{{ $message }}</small> @enderror
            </div>
            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="remember" id="remember" />
              <label class="form-check-label" for="remember">Ingat saya</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Masuk</button>
          </form>
        </div>
      </div>
    </div>
  </body>
</html>
