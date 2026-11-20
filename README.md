# Bioskop Online

Aplikasi pemesanan tiket bioskop sederhana berbasis PHP dan MySQL.

## Persyaratan

- PHP 8.x dengan ekstensi mysqli dan fileinfo
- MySQL/MariaDB
- Apache/XAMPP

## Instalasi

1. Jalankan `database.sql` untuk membuat database dan tabel.
2. Letakkan folder proyek di `htdocs/databasebioskop`.
3. Sesuaikan variabel database pada `config/koneksi.php` atau set environment `DB_HOST`, `DB_USER`, `DB_PASSWORD`, dan `DB_NAME`.
4. Buka `http://localhost/databasebioskop/`.

## Alur

- Pemirsa memilih film dan kursi di `pesan.php`.
- Bukti pembayaran diproses oleh `upload_bukti.php`.
- Admin menyetujui pesanan melalui `admin/admin.php`.
