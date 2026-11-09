<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label" for="code">Kode produk</label>
    <input class="form-control" type="text" name="code" id="code" value="{{ old('code', $product->code ?? '') }}" required>
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label" for="category_id">Kategori</label>
    <select class="form-select" name="category_id" id="category_id">
      <option value="">-- pilih kategori --</option>
      @foreach ($categories as $c)
        <option value="{{ $c->id }}" {{ old('category_id', $product->category_id ?? '') == $c->id ? 'selected' : '' }}>{{ $c->name }}</option>
      @endforeach
    </select>
  </div>
</div>
<div class="mb-3">
  <label class="form-label" for="name">Nama produk</label>
  <input class="form-control" type="text" name="name" id="name" value="{{ old('name', $product->name ?? '') }}" required>
</div>
<div class="row">
  <div class="col-md-6 mb-3">
    <label class="form-label" for="cost_price">Harga beli (Rp)</label>
    <input class="form-control" type="number" name="cost_price" id="cost_price" min="0" value="{{ old('cost_price', $product->cost_price ?? 0) }}">
  </div>
  <div class="col-md-6 mb-3">
    <label class="form-label" for="selling_price">Harga jual (Rp)</label>
    <input class="form-control" type="number" name="selling_price" id="selling_price" min="0" value="{{ old('selling_price', $product->selling_price ?? 0) }}">
  </div>
</div>
<div class="mb-3">
  <label class="form-label" for="unit">Satuan</label>
  <input class="form-control" type="text" name="unit" id="unit" value="{{ old('unit', $product->unit ?? 'pcs') }}">
</div>
