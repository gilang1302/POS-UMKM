// Halaman kasir: pilih produk, keranjang, hitung total & kembalian.
// Data produk dibaca dari atribut data-* pada tombol produk (lihat transactions/pos.blade.php)

(function () {
  var cart = [];          // [{id, name, price, qty, stock}]
  var activeCategory = '';

  var tiles = document.querySelectorAll('.kotak-produk');
  var cartBox = document.getElementById('cart-items');
  var totalText = document.getElementById('total-text');
  var changeText = document.getElementById('change-text');
  var paidInput = document.getElementById('paid-amount');
  var paidGroup = document.getElementById('paid-group');
  var qrisNote = document.getElementById('qris-note');
  var changeRow = document.getElementById('change-row');
  var submitBtn = document.getElementById('submit-btn');
  var hint = document.getElementById('submit-hint');
  var form = document.getElementById('pos-form');
  var methodRadios = document.querySelectorAll('input[name="payment_method"]');

  var products = {};
  tiles.forEach(function (tile) {
    products[tile.dataset.id] = {
      id: parseInt(tile.dataset.id),
      name: tile.dataset.name,
      price: parseInt(tile.dataset.price),
      stock: parseInt(tile.dataset.stock),
      tile: tile
    };
  });

  function getMethod() {
    var checked = document.querySelector('input[name="payment_method"]:checked');
    return checked ? checked.value : 'cash';
  }

  function getTotal() {
    return cart.reduce(function (sum, item) { return sum + item.price * item.qty; }, 0);
  }

  function findItem(id) {
    for (var i = 0; i < cart.length; i++) {
      if (cart[i].id === id) return cart[i];
    }
    return null;
  }

  function addToCart(id) {
    var p = products[id];
    var item = findItem(p.id);

    if (item) {
      if (item.qty >= p.stock) return;   // tidak boleh lebih dari stok
      item.qty++;
    } else {
      cart.push({ id: p.id, name: p.name, price: p.price, qty: 1, stock: p.stock });
    }

    render();
  }

  function changeQty(id, delta) {
    var item = findItem(id);
    if (!item) return;

    item.qty += delta;

    if (item.qty > item.stock) item.qty = item.stock;
    if (item.qty < 1) cart = cart.filter(function (c) { return c.id !== id; });

    render();
  }

  function render() {
    cartBox.innerHTML = '';

    if (cart.length === 0) {
      cartBox.appendChild(el('p', { class: 'text-muted text-center my-3', text: 'Belum ada barang. Ketuk produk di sebelah kiri.' }));
    }

    cart.forEach(function (item, i) {
      var minus = el('button', { type: 'button', class: 'btn btn-sm btn-outline-secondary', 'aria-label': 'Kurangi ' + item.name, text: '-' });
      var plus = el('button', { type: 'button', class: 'btn btn-sm btn-outline-secondary', 'aria-label': 'Tambah ' + item.name, text: '+' });
      var del = el('button', { type: 'button', class: 'btn btn-sm btn-danger', 'aria-label': 'Hapus ' + item.name, text: 'x' });

      minus.addEventListener('click', function () { changeQty(item.id, -1); });
      plus.addEventListener('click', function () { changeQty(item.id, 1); });
      del.addEventListener('click', function () { changeQty(item.id, -999); });

      var row = el('div', { class: 'border-bottom pb-2 mb-2' }, [
        el('div', { class: 'd-flex justify-content-between' }, [
          el('b', { text: item.name }),
          el('span', { text: formatRupiah(item.price * item.qty) })
        ]),
        el('div', { class: 'd-flex justify-content-between align-items-center mt-1' }, [
          el('div', { class: 'd-flex align-items-center gap-2' }, [
            minus,
            el('span', { class: 'small', text: item.qty + ' x ' + formatRupiah(item.price) }),
            plus
          ]),
          del
        ]),
        el('input', { type: 'hidden', name: 'items[' + i + '][product_id]', value: item.id }),
        el('input', { type: 'hidden', name: 'items[' + i + '][qty]', value: item.qty })
      ]);

      cartBox.appendChild(row);
    });

    // tanda jumlah di tiap tile
    Object.keys(products).forEach(function (id) {
      var p = products[id];
      var item = findItem(p.id);
      var badge = p.tile.querySelector('.jml');

      p.tile.querySelector('.kartu-produk').classList.toggle('habis', !!item && item.qty >= p.stock);
      badge.style.display = item ? '' : 'none';
      badge.textContent = item ? item.qty + ' di pesanan' : '';
    });

    totalText.textContent = formatRupiah(getTotal());
    refreshPayment();
  }

  function refreshPayment() {
    var total = getTotal();
    var isCash = getMethod() === 'cash';
    var paid = parseInt(paidInput.value) || 0;

    paidGroup.style.display = isCash ? '' : 'none';
    changeRow.style.display = isCash ? '' : 'none';
    qrisNote.style.display = isCash ? 'none' : '';

    changeText.textContent = formatRupiah(Math.max(paid - total, 0));

    var message = '';
    if (cart.length === 0) {
      message = 'Pilih produk dulu';
    } else if (isCash && paid < total) {
      message = 'Uang bayar masih kurang ' + formatRupiah(total - paid);
    }

    submitBtn.disabled = message !== '';
    hint.textContent = message;
    submitBtn.querySelector('span').textContent = isCash ? 'Bayar tunai' : 'Buat kode QRIS';
  }

  // ---------- filter produk (kategori + pencarian) ----------
  function filterTiles() {
    var keyword = document.getElementById('cari-produk').value.toLowerCase();

    tiles.forEach(function (tile) {
      var matchCat = activeCategory === '' || tile.dataset.cat === activeCategory;
      var matchText = tile.dataset.name.toLowerCase().indexOf(keyword) !== -1;
      tile.style.display = matchCat && matchText ? '' : 'none';   // kotak produk (kolom grid)
    });
  }

  tiles.forEach(function (tile) {
    tile.addEventListener('click', function () { addToCart(tile.dataset.id); });
  });

  document.querySelectorAll('.btn-kategori').forEach(function (chip) {
    chip.addEventListener('click', function () {
      document.querySelectorAll('.btn-kategori').forEach(function (c) {
        c.classList.remove('btn-primary');
        c.classList.add('btn-outline-primary');
      });
      chip.classList.remove('btn-outline-primary');
      chip.classList.add('btn-primary');
      activeCategory = chip.dataset.cat;
      filterTiles();
    });
  });

  document.getElementById('cari-produk').addEventListener('input', filterTiles);

  // uang cepat: uang pas / nominal tertentu
  document.querySelectorAll('[data-uang]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var value = btn.dataset.uang === 'pas' ? getTotal() : parseInt(btn.dataset.uang);
      paidInput.value = value;
      refreshPayment();
    });
  });

  paidInput.addEventListener('input', refreshPayment);
  methodRadios.forEach(function (r) { r.addEventListener('change', refreshPayment); });

  form.addEventListener('submit', function (e) {
    if (submitBtn.disabled) e.preventDefault();
  });

  // kalau server menolak (misalnya stok kurang), isi keranjang dipulihkan
  (window.POS_OLD_ITEMS || []).forEach(function (old) {
    var p = products[old.product_id];
    if (p) {
      cart.push({ id: p.id, name: p.name, price: p.price, qty: Math.min(parseInt(old.qty) || 1, p.stock), stock: p.stock });
    }
  });

  render();
})();
