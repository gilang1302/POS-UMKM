// Dashboard: mengisi kartu, grafik, dan daftar dari data JSON, lalu memperbarui otomatis tiap 15 detik

(function () {
  var data = window.DASH_DATA;
  var url = window.DASH_URL;
  var chart = null;

  function setText(id, text) {
    document.getElementById(id).textContent = text;
  }

  // 1.250.000 -> 1,25 jt
  function shortMoney(v) {
    if (v === 0) return '0';
    if (v >= 1000000) return (v / 1000000).toFixed(2).replace('.', ',').replace(/,?0+$/, '') + ' jt';
    return Math.round(v / 1000) + ' rb';
  }

  // baris kosong untuk daftar yang belum ada isinya
  function emptyText(text) {
    return el('p', { class: 'text-muted mb-0', text: text });
  }

  // satu baris daftar: kiri nama + keterangan kecil, kanan angka
  function row(left, small, right) {
    return el('div', { class: 'd-flex justify-content-between align-items-center py-2 border-bottom' }, [
      el('div', {}, [left, el('div', { class: 'small text-muted', text: small || '' })]),
      right
    ]);
  }

  function drawChart() {
    var colors = data.day_revenue.map(function (v, i) {
      return i === data.day_revenue.length - 1 ? '#1d7af3' : '#a9c7f5';
    });

    if (chart) {
      chart.data.labels = data.days;
      chart.data.datasets[0].data = data.day_revenue;
      chart.data.datasets[0].backgroundColor = colors;
      chart.update();
      return;
    }

    chart = new Chart(document.getElementById('grafik-omset').getContext('2d'), {
      type: 'bar',
      data: {
        labels: data.days,
        datasets: [{ label: 'Omset', data: data.day_revenue, backgroundColor: colors }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        legend: { display: false },
        tooltips: {
          callbacks: {
            label: function (item) { return formatRupiah(item.yLabel); }
          }
        },
        scales: {
          yAxes: [{ ticks: { beginAtZero: true, callback: function (v) { return shortMoney(v); } } }]
        }
      }
    });
  }

  function drawBranches() {
    var box = document.getElementById('omset-cabang');
    box.innerHTML = '';

    var max = Math.max.apply(null, data.branches.map(function (b) { return b.total; }).concat([1]));

    data.branches.forEach(function (b) {
      box.appendChild(el('div', { class: 'mb-3' }, [
        el('div', { class: 'd-flex justify-content-between' }, [
          el('span', { text: b.name + ' (' + b.count + ' transaksi)' }),
          el('b', { text: formatRupiah(b.total) })
        ]),
        el('div', { class: 'progress', style: 'height: 8px' }, [
          el('div', { class: 'progress-bar bg-primary', style: 'width:' + Math.round((b.total / max) * 100) + '%' })
        ])
      ]));
    });

    if (data.branches.length === 0) box.appendChild(emptyText('Belum ada cabang.'));
  }

  function drawTop() {
    var box = document.getElementById('produk-terlaris');
    box.innerHTML = '';

    data.top_products.forEach(function (p) {
      box.appendChild(row(el('span', { text: p.name }), '', el('b', { text: p.qty + ' terjual' })));
    });

    if (data.top_products.length === 0) box.appendChild(emptyText('Belum ada penjualan hari ini.'));
  }

  function drawLatest() {
    var box = document.getElementById('transaksi-terakhir');
    box.innerHTML = '';

    var badge = { paid: 'badge-success', pending: 'badge-warning', cancelled: 'badge-secondary', expired: 'badge-secondary' };

    data.latest.forEach(function (t) {
      var right = el('div', { class: 'text-end' }, [
        el('b', { text: formatRupiah(t.total) }),
        el('div', {}, [el('span', { class: 'badge ' + (badge[t.status] || 'badge-secondary'), text: t.status_label })])
      ]);

      box.appendChild(row(el('a', { href: t.url, text: t.invoice }), t.branch + ', ' + t.time, right));
    });

    if (data.latest.length === 0) box.appendChild(emptyText('Belum ada transaksi.'));
  }

  function drawAlerts() {
    var box = document.getElementById('stok-menipis');
    box.innerHTML = '';

    data.alerts.forEach(function (a) {
      var empty = a.quantity === 0;
      var label = empty ? 'Habis' : 'Sisa ' + a.quantity + ' (min. ' + a.min_stock + ')';

      box.appendChild(row(
        el('b', { text: a.product }),
        a.branch,
        el('span', { class: 'badge ' + (empty ? 'badge-danger' : 'badge-warning'), text: label })
      ));
    });

    if (data.alerts.length === 0) box.appendChild(emptyText('Semua stok di atas batas minimum.'));
  }

  function render() {
    setText('angka-omset', formatRupiah(data.revenue));
    setText('angka-transaksi', data.count);
    setText('angka-rata', formatRupiah(data.average));
    setText('angka-qris', data.pending_qris);
    setText('angka-alert', data.alert_count);
    setText('waktu-update', data.updated_at);

    drawChart();
    drawBranches();
    drawTop();
    drawLatest();
    drawAlerts();
  }

  function refresh() {
    fetch(url, { headers: { 'Accept': 'application/json' }, credentials: 'same-origin' })
      .then(function (res) { return res.ok ? res.json() : null; })
      .then(function (json) {
        if (json) {
          data = json;
          render();
        }
      })
      .catch(function () { /* koneksi putus, coba lagi di putaran berikutnya */ });
  }

  render();
  setInterval(refresh, 15000);
})();
