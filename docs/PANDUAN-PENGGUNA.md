# Panduan Pengguna

Panduan singkat memakai POS & Inventaris Multi-Cabang UMKM. Buka aplikasi di http://localhost:8000 lalu masuk dengan email dan kata sandi.

## 1. Masuk dan keluar

1. Isi email dan kata sandi, tekan **Masuk**.
2. Untuk keluar, klik nama Anda di pojok kanan atas, lalu pilih **Keluar**.

Menu ada di sisi kiri. Owner melihat semua menu. Kasir hanya melihat Dashboard, Kasir (POS), Riwayat Transaksi, Stok Barang, Kartu Stok, dan Stok Menipis untuk cabangnya sendiri.

## 2. Melayani pembeli di Kasir

1. Buka menu **Kasir (POS)**. Owner memilih cabang dulu di kanan atas.
2. Ketuk produk untuk memasukkannya ke Pesanan. Ketuk lagi untuk menambah jumlah. Gunakan kolom pencarian atau tombol kategori untuk mencari produk.
3. Pilih cara bayar.

**Tunai**
- Isi uang yang diterima, atau tekan tombol cepat (Uang pas, 20.000, dan seterusnya).
- Kembalian muncul otomatis. Tekan **Bayar tunai**.

**QRIS**
- Pilih QRIS, lalu tekan **Buat kode QRIS**. Halaman kode QR muncul dan berlaku 5 menit.
- Stok barang langsung ditahan supaya tidak terjual ke orang lain.
- Setelah pembeli selesai membayar, tekan **Simulasikan pembayaran berhasil**.
- Kalau pembeli batal, tekan **Batalkan pesanan**. Jika waktu habis, pesanan kedaluwarsa dan stok dikembalikan otomatis.

4. Setelah lunas, tekan **Cetak struk** di halaman detail transaksi.

Jika stok tidak cukup, aplikasi menolak transaksi dan menampilkan sisa stok.

## 3. Riwayat transaksi

Menu **Riwayat Transaksi** menampilkan semua transaksi. Cari berdasarkan nomor invoice atau filter status (Lunas, Menunggu bayar, Dibatalkan, Kedaluwarsa). Ketuk nomor invoice untuk melihat detail.

## 4. Mengelola stok (owner)

Bagian **Inventaris** di menu kiri punya beberapa halaman:

- **Stok Barang**: daftar stok per cabang atau gudang. Pilih lokasi di kanan atas.
- **Barang masuk**: tambah stok saat ada pembelian dari pemasok.
- **Sesuaikan**: isi jumlah hasil hitung fisik (stok opname) dan catatannya.
- **Kartu Stok**: riwayat berisi semua pergerakan barang (penjualan, barang masuk, transfer, penyesuaian).
- **Transfer Antar Cabang**: pindahkan barang antar gudang dan cabang. Pilih lokasi asal dan tujuan, tambah produk beserta jumlahnya, lalu tekan **Proses transfer**. Stok kedua lokasi berubah sekaligus.
- **Stok Menipis**: daftar barang yang sudah di bawah batas minimum.

Batas minimum (kolom Stok minimum) diatur per produk di halaman **Barang masuk** atau **Sesuaikan**.

## 5. Notifikasi stok menipis

Ikon lonceng di kanan atas menampilkan jumlah peringatan yang belum dibaca. Peringatan muncul saat stok sama dengan atau di bawah batas minimum, dan hilang sendiri setelah stok ditambah.

## 6. Dashboard (owner)

Dashboard menampilkan omset hari ini, jumlah transaksi, rata-rata per transaksi, QRIS yang masih menunggu, grafik omset 7 hari, omset per cabang, produk terlaris, dan transaksi terakhir. Halaman memperbarui diri tiap 15 detik tanpa perlu dimuat ulang.

## 7. Laporan (owner)

1. Buka menu **Laporan Penjualan**.
2. Pilih **Harian** atau **Bulanan**, tanggal atau bulannya, dan cabang (boleh semua cabang).
3. Tekan **Tampilkan**. Ringkasan berisi omset, harga pokok, laba kotor, serta pembagian tunai dan QRIS.
4. Tekan **Unduh PDF** atau **Unduh Excel** untuk menyimpan laporan.

Hanya transaksi yang sudah lunas yang dihitung sebagai omset.

## 8. Data master (owner)

Bagian **Data Master** di menu kiri berisi:

- **Cabang & Gudang**: tambah atau ubah lokasi. Lokasi yang sudah punya stok atau transaksi tidak bisa dihapus.
- **Kategori**: kelompok produk.
- **Produk**: kode, nama, harga beli, harga jual, dan satuan.
- **Pengguna**: akun owner dan kasir. Kasir wajib dipilihkan cabangnya.

## 9. Masalah yang sering muncul

| Masalah | Penyebab dan cara mengatasi |
|---------|-----------------------------|
| Produk tidak muncul di Kasir | Stok cabang itu 0. Tambah stok lewat Barang masuk atau Transfer |
| "Stok ... tidak cukup" | Jumlah yang dipesan melebihi stok, kurangi jumlahnya |
| "Uang bayar kurang dari total belanja" | Isi uang yang diterima sesuai atau lebih dari total |
| QRIS sudah kedaluwarsa | Buat pesanan baru, stok sudah dikembalikan |
| Kasir tidak bisa membuka laporan | Menu itu khusus owner |
