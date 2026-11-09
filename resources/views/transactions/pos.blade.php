@extends('layouts.app')

@section('title', 'Kasir (POS)')

@section('content')
<div class="row">

  {{-- daftar produk --}}
  <div class="col-md-7">
    <div class="card">
      <div class="card-header">
        <h4 class="card-title">Pilih Produk</h4>
        @if (auth()->user()->isOwner())
          <form action="{{ route('pos') }}" method="GET">
            <select name="branch_id" class="form-select w-50" onchange="this.form.submit()">
              @foreach ($branches as $b)
                <option value="{{ $b->id }}" {{ $branch_id == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
              @endforeach
            </select>
          </form>
        @endif
      </div>
      <div class="card-body">
        <table class="table table-hover">
          <thead>
            <tr>
              <th>Produk</th>
              <th>Harga</th>
              <th>Stok</th>
              <th></th>
            </tr>
          </thead>
          <tbody>
            @forelse ($stocks as $s)
              <tr>
                <td>{{ $s->product->name }}</td>
                <td>Rp {{ number_format($s->product->selling_price, 0, ',', '.') }}</td>
                <td>{{ $s->quantity }}</td>
                <td>
                  <button type="button" class="btn btn-sm btn-primary"
                    onclick="addToCart({{ $s->product_id }}, '{{ addslashes($s->product->name) }}', {{ $s->product->selling_price }}, {{ $s->quantity }})">
                    <i class="fa fa-plus"></i>
                  </button>
                </td>
              </tr>
            @empty
              <tr><td colspan="4" class="text-center text-muted">Belum ada stok di cabang ini</td></tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- keranjang --}}
  <div class="col-md-5">
    <div class="card">
      <div class="card-header"><h4 class="card-title">Keranjang</h4></div>
      <div class="card-body">
        <form action="{{ route('pos.store') }}" method="POST" id="pos-form">
          @csrf
          <input type="hidden" name="branch_id" value="{{ $branch_id }}">

          <table class="table table-sm">
            <thead>
              <tr>
                <th>Produk</th>
                <th width="80">Qty</th>
                <th>Subtotal</th>
                <th></th>
              </tr>
            </thead>
            <tbody id="cart-body">
              <tr><td colspan="4" class="text-center text-muted">Keranjang kosong</td></tr>
            </tbody>
          </table>

          <h4 class="text-end">Total: <span id="total-text">Rp 0</span></h4>

          <div class="form-group">
            <label>Metode Bayar</label>
            <select name="payment_method" class="form-select">
              <option value="cash">Tunai</option>
              <option value="qris">QRIS (belum tersedia)</option>
            </select>
          </div>
          <div class="form-group">
            <label>Uang Bayar</label>
            <input type="number" name="paid_amount" id="paid-amount" class="form-control" value="0">
          </div>
          <p>Kembalian: <b id="change-text">Rp 0</b></p>

          <button type="submit" class="btn btn-success w-100">
            <i class="fa fa-check"></i> Bayar
          </button>
        </form>
      </div>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
  var cart = [];

  function formatRupiah(number) {
    return 'Rp ' + number.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
  }

  function addToCart(id, name, price, stock) {
    for (var i = 0; i < cart.length; i++) {
      if (cart[i].id == id) {
        if (cart[i].qty >= stock) {
          alert('Stok ' + name + ' hanya ' + stock);
          return;
        }
        cart[i].qty++;
        renderCart();
        return;
      }
    }
    cart.push({ id: id, name: name, price: price, qty: 1, stock: stock });
    renderCart();
  }

  function changeQty(index, qty) {
    qty = parseInt(qty);
    if (isNaN(qty) || qty < 1) qty = 1;
    if (qty > cart[index].stock) {
      alert('Stok tidak cukup');
      qty = cart[index].stock;
    }
    cart[index].qty = qty;
    renderCart();
  }

  function removeItem(index) {
    cart.splice(index, 1);
    renderCart();
  }

  function renderCart() {
    var html = '';
    var total = 0;

    for (var i = 0; i < cart.length; i++) {
      var item = cart[i];
      var subtotal = item.price * item.qty;
      total += subtotal;

      html += '<tr>';
      html += '<td>' + item.name + '<input type="hidden" name="items[' + i + '][product_id]" value="' + item.id + '"></td>';
      html += '<td><input type="number" name="items[' + i + '][qty]" value="' + item.qty + '" min="1" class="form-control form-control-sm" onchange="changeQty(' + i + ', this.value)"></td>';
      html += '<td>' + formatRupiah(subtotal) + '</td>';
      html += '<td><button type="button" class="btn btn-sm btn-danger" onclick="removeItem(' + i + ')">x</button></td>';
      html += '</tr>';
    }

    if (cart.length == 0) {
      html = '<tr><td colspan="4" class="text-center text-muted">Keranjang kosong</td></tr>';
    }

    document.getElementById('cart-body').innerHTML = html;
    document.getElementById('total-text').innerText = formatRupiah(total);
    calculateChange();
  }

  function calculateChange() {
    var total = 0;
    for (var i = 0; i < cart.length; i++) {
      total += cart[i].price * cart[i].qty;
    }
    var paid = parseInt(document.getElementById('paid-amount').value) || 0;
    var change = paid - total;
    if (change < 0) change = 0;
    document.getElementById('change-text').innerText = formatRupiah(change);
  }

  document.getElementById('paid-amount').addEventListener('input', calculateChange);

  document.getElementById('pos-form').addEventListener('submit', function (e) {
    if (cart.length == 0) {
      e.preventDefault();
      alert('Keranjang masih kosong!');
    }
  });
</script>
@endpush
