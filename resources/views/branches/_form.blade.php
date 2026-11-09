<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label" for="code">Kode</label>
    <input class="form-control" type="text" name="code" id="code" value="{{ old('code', $branch->code ?? '') }}" maxlength="10" required>
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label" for="type">Tipe</label>
    <select class="form-select" name="type" id="type">
      <option value="branch" {{ old('type', $branch->type ?? '') == 'branch' ? 'selected' : '' }}>Cabang (tempat jualan)</option>
      <option value="warehouse" {{ old('type', $branch->type ?? '') == 'warehouse' ? 'selected' : '' }}>Gudang</option>
    </select>
  </div>
</div>
<div class="mb-3">
  <label class="form-label" for="name">Nama</label>
  <input class="form-control" type="text" name="name" id="name" value="{{ old('name', $branch->name ?? '') }}" required>
</div>
<div class="mb-3">
  <label class="form-label" for="address">Alamat</label>
  <textarea class="form-control" name="address" id="address" rows="3">{{ old('address', $branch->address ?? '') }}</textarea>
</div>
<div class="mb-3">
  <label class="form-label" for="phone">Telepon</label>
  <input class="form-control" type="text" name="phone" id="phone" value="{{ old('phone', $branch->phone ?? '') }}">
</div>
