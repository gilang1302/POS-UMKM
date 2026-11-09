@php
  $roleNow = old('role', $user->role ?? 'cashier');
@endphp

<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label" for="name">Nama</label>
    <input class="form-control" type="text" name="name" id="name" value="{{ old('name', $user->name ?? '') }}" required>
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label" for="email">Email</label>
    <input class="form-control" type="email" name="email" id="email" value="{{ old('email', $user->email ?? '') }}" required>
  </div>
</div>

<div class="mb-3">
  <label class="form-label" for="password">Kata sandi</label>
  <input class="form-control" type="password" name="password" id="password" autocomplete="new-password" {{ isset($user) ? '' : 'required' }}>
  <div class="form-text">{{ isset($user) ? 'Kosongkan kalau tidak ingin mengganti. ' : '' }}Minimal 6 karakter.</div>
</div>

<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label" for="role">Peran</label>
    <select class="form-select" name="role" id="role">
      <option value="cashier" {{ $roleNow == 'cashier' ? 'selected' : '' }}>Kasir</option>
      <option value="owner" {{ $roleNow == 'owner' ? 'selected' : '' }}>Owner</option>
    </select>
  </div>
  <div class="col-md-6 mb-3" id="grup-cabang">
    <label class="form-label" for="branch_id">Cabang tempat bertugas</label>
    <select class="form-select" name="branch_id" id="branch_id">
      <option value="">-- pilih cabang --</option>
      @foreach ($branches as $b)
        <option value="{{ $b->id }}" {{ old('branch_id', $user->branch_id ?? '') == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
      @endforeach
    </select>
  </div>
</div>

<script>
  // kolom cabang hanya perlu untuk kasir
  (function () {
    var role = document.getElementById('role');
    var group = document.getElementById('grup-cabang');

    function toggle() {
      group.style.display = role.value === 'cashier' ? '' : 'none';
    }

    role.addEventListener('change', toggle);
    toggle();
  })();
</script>
