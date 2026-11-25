# TiketWar — Tugas Akhir Pemrograman Aplikasi Web (Kelompok 1)

Aplikasi penjualan tiket konser. Admin mengelola promotor, konser, pesanan, dan laporan penjualan
lewat Laravel (Blade). Pelanggan melihat daftar konser dan memesan tiket lewat aplikasi React yang
memakai API Laravel.

## Anggota Kelompok

| Nama | NPM | Peran |
|---|---|---|
| Dylan Arya Immanuel Suhadi | 240712776 | [ISI PEMBAGIAN] |
| [NAMA ANGGOTA 2] | [NPM] | [ISI PEMBAGIAN] |

## Fitur

### Fitur dasar (sudah ada)
- CRUD promotor dan konser dengan validasi di server.
- Pemesanan tiket oleh pelanggan tanpa login: stok berkurang, harga dan total dihitung server.
- Admin menandai pesanan **lunas** atau **batal**; pembatalan mengembalikan stok.
- Laporan penjualan dengan filter status/tanggal dan ekspor PDF.
- Login admin dengan akun dari `.env`, pembatasan percobaan login, dan halaman ganti kata sandi.
- API publik: daftar konser, detail konser, buat pesanan.
- API admin (token Sanctum): kelola konser, ubah status pesanan, ringkasan laporan.

### Fitur pengembangan
| Kode | Fitur | Status | Dikerjakan oleh |
|---|---|---|---|
| A | Kategori Tiket (VIP/Reguler, harga & stok per jenis) | Belum | [ISI] |
| I | Pembatasan Pembelian per pesanan | Belum | [ISI] |
| G | Riwayat Pesanan lewat kode pesanan | Belum | [ISI] |
| J | Log Aktivitas Admin | Belum (menunggu kepastian dosen) | [ISI] |

## Kebijakan Kelompok

Keputusan yang diambil kelompok dan berlaku di aplikasi:

| Kebijakan | Nilai | Keterangan |
|---|---|---|
| Batas pembelian per pesanan | 5 tiket | Diterapkan pada Fitur I; diatur di config, bukan angka di controller. |
| Jenis tiket bawaan | VIP dan Reguler | Diterapkan pada Fitur A; dibuat seeder untuk setiap konser. |
| Kode pesanan | 12 karakter acak | Diterapkan pada Fitur G; dipakai pelanggan untuk mengecek status pesanan. |

Nilai di atas adalah rancangan yang disepakati kelompok. Setiap baris diperbarui saat fitur terkait
selesai dikerjakan.

## Teknologi

- Laravel 13 + PHP 8.3 (MySQL, Blade, Sanctum, DomPDF)
- React 19 + Vite (folder `react/`)

## Struktur Folder

```
app/                 kode Laravel (controller, model, resource)
database/            migration dan seeder
resources/views/     tampilan Blade untuk admin dan pelanggan
routes/              web.php (Blade) dan api.php (API)
react/               aplikasi React untuk pelanggan
docs/                catatan praktikum dan jawaban latihan modul
```

## Cara Menjalankan

Prasyarat: PHP 8.3+, Composer, Node.js 20+, MySQL (XAMPP) menyala.

1. Nyalakan **MySQL** di XAMPP, lalu buat database `tiketwar` (mis. lewat phpMyAdmin).
2. Pasang dependensi PHP:
   ```
   composer install
   ```
3. Salin berkas lingkungan:
   ```
   copy .env.example .env        (Windows)
   cp .env.example .env          (macOS/Linux)
   ```
4. Isi `ADMIN_EMAIL` dan `ADMIN_PASSWORD` di `.env` (lihat bagian berikut), lalu:
   ```
   php artisan key:generate
   ```
5. Siapkan tabel dan data contoh:
   ```
   php artisan migrate:fresh --seed
   ```
6. Jalankan server Laravel:
   ```
   php artisan serve
   ```
   Aplikasi Blade: <http://127.0.0.1:8000>
7. Jalankan aplikasi React (terminal terpisah):
   ```
   cd react
   npm install
   npm run dev
   ```
   Aplikasi React: <http://localhost:5173> (permintaan `/api` diteruskan ke Laravel oleh Vite).

### Database untuk pengujian

Test memakai database MySQL terpisah bernama `tiketwar_test` (lihat `phpunit.xml`) supaya data
`tiketwar` tidak terhapus. Buat sekali:
```
mysql -u root -e "CREATE DATABASE tiketwar_test"
```
Lalu jalankan:
```
php artisan test
```

## Membuat Akun Admin

Akun admin dibuat oleh `AdminSeeder` dari nilai di `.env`, bukan ditulis di dalam kode:

1. Buka `.env`, isi `ADMIN_EMAIL` dengan email pilihanmu dan `ADMIN_PASSWORD` dengan kata sandi
   yang kuat. Nilai ini tidak boleh di-commit.
2. Jalankan `php artisan migrate:fresh --seed` (atau `php artisan db:seed --class=AdminSeeder`).
3. Login di `/login` memakai email dan kata sandi tersebut.

Mengganti kata sandi lewat halaman **Ganti Password** hanya mengubah database. Bila
`migrate:fresh --seed` dijalankan lagi, kata sandi kembali ke nilai `ADMIN_PASSWORD` di `.env`,
jadi perbarui juga nilai itu bila ingin kata sandi baru tetap dipakai.

## Alamat Hosting

Belum tersedia. [ISI ALAMAT HOSTING SETELAH DEPLOY]

## Catatan Praktikum

Jawaban latihan, catatan debugging, dan audit keamanan dari modul-modul sebelumnya ada di
[docs/catatan-praktikum.md](docs/catatan-praktikum.md).
