<div class="form-group">
  <label>Kode Cabang</label>
  <input type="text" name="code" class="form-control" value="{{ old('code', $branch->code ?? '') }}" maxlength="10">
</div>
<div class="form-group">
  <label>Nama Cabang</label>
  <input type="text" name="name" class="form-control" value="{{ old('name', $branch->name ?? '') }}">
</div>
<div class="form-group">
  <label>Tipe</label>
  <select name="type" class="form-select">
    <option value="branch" {{ old('type', $branch->type ?? '') == 'branch' ? 'selected' : '' }}>Cabang (toko)</option>
    <option value="warehouse" {{ old('type', $branch->type ?? '') == 'warehouse' ? 'selected' : '' }}>Gudang</option>
  </select>
</div>
<div class="form-group">
  <label>Alamat</label>
  <textarea name="address" class="form-control" rows="3">{{ old('address', $branch->address ?? '') }}</textarea>
</div>
<div class="form-group">
  <label>Telepon</label>
  <input type="text" name="phone" class="form-control" value="{{ old('phone', $branch->phone ?? '') }}">
</div>
