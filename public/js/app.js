// Script umum yang dipakai semua halaman: pencarian tabel dan konfirmasi hapus

function formatRupiah(number) {
  return 'Rp ' + Math.round(number).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
}

// buat elemen HTML: el('div', {class: 'x'}, ['teks', elemenLain])
function el(tag, attrs, children) {
  var node = document.createElement(tag);
  attrs = attrs || {};

  Object.keys(attrs).forEach(function (key) {
    if (key === 'text') {
      node.textContent = attrs[key];
    } else {
      node.setAttribute(key, attrs[key]);
    }
  });

  (children || []).forEach(function (child) {
    node.appendChild(typeof child === 'string' ? document.createTextNode(child) : child);
  });

  return node;
}

$(function () {

  // pencarian tabel: <input data-cari="#idTabel">
  $('[data-cari]').on('input', function () {
    var kata = $(this).val().toLowerCase();

    $($(this).attr('data-cari')).find('tbody tr').each(function () {
      if ($(this).hasClass('baris-kosong')) return;
      $(this).toggle($(this).text().toLowerCase().indexOf(kata) !== -1);
    });
  });

  // konfirmasi sebelum kirim form: <form data-konfirmasi="Yakin hapus?">
  $('form[data-konfirmasi]').on('submit', function (e) {
    var form = this;

    if ($(form).data('sudah')) return;
    e.preventDefault();

    swal({
      title: 'Konfirmasi',
      text: $(form).attr('data-konfirmasi'),
      icon: 'warning',
      buttons: {
        cancel: { visible: true, text: 'Batal', className: 'btn btn-secondary' },
        confirm: { text: 'Ya, lanjutkan', className: 'btn btn-danger' },
      },
    }).then(function (setuju) {
      if (setuju) {
        $(form).data('sudah', true);
        form.submit();
      }
    });
  });

});
