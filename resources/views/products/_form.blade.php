<div class="row">
  <div class="col-md-6">
    <div class="form-group">
      <label>Kode Produk</label>
      <input type="text" name="code" class="form-control" value="{{ old('code', $product->code ?? '') }}">
    </div>
    <div class="form-group">
      <label>Nama Produk</label>
      <input type="text" name="name" class="form-control" value="{{ old('name', $product->name ?? '') }}">
    </div>
    <div class="form-group">
      <label>Kategori</label>
      <select name="category_id" class="form-select">
        <option value="">-- pilih kategori --</option>
        @foreach ($categories as $c)
          <option value="{{ $c->id }}" {{ old('category_id', $product->category_id ?? '') == $c->id ? 'selected' : '' }}>
            {{ $c->name }}
          </option>
        @endforeach
      </select>
    </div>
  </div>
  <div class="col-md-6">
    <div class="form-group">
      <label>Harga Beli</label>
      <input type="number" name="cost_price" class="form-control" value="{{ old('cost_price', $product->cost_price ?? 0) }}">
    </div>
    <div class="form-group">
      <label>Harga Jual</label>
      <input type="number" name="selling_price" class="form-control" value="{{ old('selling_price', $product->selling_price ?? 0) }}">
    </div>
    <div class="form-group">
      <label>Satuan</label>
      <input type="text" name="unit" class="form-control" value="{{ old('unit', $product->unit ?? 'pcs') }}">
    </div>
  </div>
</div>
