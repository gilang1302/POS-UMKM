// Hitung mundur masa berlaku kode QRIS. Kalau habis, muat ulang halaman
// supaya server membatalkan pesanan dan mengembalikan stok.

(function () {
  var box = document.getElementById('hitung-mundur');
  var endTime = parseInt(box.dataset.berakhir);

  function tick() {
    var left = Math.floor((endTime - Date.now()) / 1000);

    if (left <= 0) {
      box.textContent = '00:00';
      window.location.reload();
      return;
    }

    var minutes = Math.floor(left / 60);
    var seconds = left % 60;
    box.textContent = (minutes < 10 ? '0' : '') + minutes + ':' + (seconds < 10 ? '0' : '') + seconds;
    box.classList.toggle('text-danger', left <= 60);

    setTimeout(tick, 1000);
  }

  tick();
})();
