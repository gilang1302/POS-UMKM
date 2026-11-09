// Form transfer stok: daftar produk mengikuti stok yang ada di lokasi asal

(function () {
  var stockMap = window.TRANSFER_STOCK || {};   // {idCabang: [{product_id, code, name, qty, unit}]}
  var from = document.getElementById('from_branch_id');
  var to = document.getElementById('to_branch_id');
  var body = document.getElementById('item-body');
  var addBtn = document.getElementById('add-item');
  var form = document.getElementById('transfer-form');
  var rowCount = 0;

  function sourceStock() {
    return stockMap[from.value] || [];
  }

  function findStock(productId) {
    var list = sourceStock();
    for (var i = 0; i < list.length; i++) {
      if (String(list[i].product_id) === String(productId)) return list[i];
    }
    return null;
  }

  function addRow(productId, qty) {
    var list = sourceStock();
    if (list.length === 0) return;

    var index = rowCount++;

    var select = el('select', { class: 'form-select', name: 'items[' + index + '][product_id]', 'aria-label': 'Produk' });
    list.forEach(function (s) {
      var opt = el('option', { value: s.product_id, text: s.code + ' - ' + s.name });
      if (productId && String(productId) === String(s.product_id)) opt.selected = true;
      select.appendChild(opt);
    });

    var qtyInput = el('input', { type: 'number', class: 'form-control', name: 'items[' + index + '][qty]', min: 1, value: qty || 1, 'aria-label': 'Jumlah' });
    var info = el('span', { class: 'form-text' });
    var del = el('button', { type: 'button', class: 'btn btn-danger btn-sm', text: 'Hapus' });

    function refreshInfo() {
      var s = findStock(select.value);
      if (!s) return;
      qtyInput.max = s.qty;
      info.textContent = 'Tersedia ' + s.qty + ' ' + s.unit;
    }

    select.addEventListener('change', refreshInfo);
    del.addEventListener('click', function () { row.remove(); toggleEmpty(); });

    var row = el('tr', {}, [
      el('td', {}, [select]),
      el('td', { style: 'width:130px' }, [qtyInput]),
      el('td', {}, [info]),
      el('td', { style: 'width:80px' }, [del])
    ]);

    body.appendChild(row);
    refreshInfo();
    toggleEmpty();
  }

  function toggleEmpty() {
    var empty = document.getElementById('item-kosong');
    empty.style.display = body.children.length === 0 ? '' : 'none';
  }

  // ganti lokasi asal: daftar produk berubah, baris lama dikosongkan
  from.addEventListener('change', function () {
    body.innerHTML = '';
    toggleEmpty();
  });

  addBtn.addEventListener('click', function () { addRow(); });

  form.addEventListener('submit', function (e) {
    if (from.value === to.value) {
      e.preventDefault();
      document.getElementById('transfer-error').textContent = 'Lokasi asal dan tujuan tidak boleh sama.';
      return;
    }
    if (body.children.length === 0) {
      e.preventDefault();
      document.getElementById('transfer-error').textContent = 'Tambahkan minimal satu produk.';
    }
  });

  // pulihkan isi form kalau server menolak
  (window.TRANSFER_OLD || []).forEach(function (old) {
    addRow(old.product_id, old.qty);
  });

  toggleEmpty();
})();
