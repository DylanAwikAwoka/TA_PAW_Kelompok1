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
| A | Kategori Tiket (VIP/Reguler, harga & stok per jenis) | Selesai | [ISI] |
| I | Pembatasan Pembelian per pesanan | Selesai | [ISI] |
| G | Riwayat Pesanan lewat kode pesanan | Selesai | [ISI] |
| J | Log Aktivitas Admin | Belum (menunggu kepastian dosen) | [ISI] |

## Kebijakan Kelompok

Keputusan yang diambil kelompok dan berlaku di aplikasi:

| Kebijakan | Nilai | Keterangan |
|---|---|---|
| Batas pembelian per pesanan | 5 tiket | Diatur di `config/tiketwar.php` dan dipakai bersama oleh web dan API. |
| Jenis tiket bawaan | VIP dan Reguler | Dibuat seeder untuk setiap konser. Harga VIP = 1,5 × harga reguler (dibulatkan). Stok VIP maksimal 5 tiket atau 20% stok konser, mana yang lebih kecil. |
| Stok konser | Total dari semua jenis | Kolom `sisa` pada konser hanya cerminan. Stok sebenarnya ada di masing-masing jenis tiket. |
| Kode pesanan | 12 karakter acak huruf besar/angka | Dibuat otomatis saat pesanan dibuat, unik, dan dipakai untuk mengecek status. |

Nilai di atas adalah rancangan yang disepakati kelompok. Setiap baris diperbarui saat fitur terkait
selesai dikerjakan.

## Fitur A — Kategori Tiket

Satu konser dapat memiliki banyak jenis tiket. Setiap jenis punya nama, harga, dan stok sendiri.
Saat memesan, harga satuan dan total selalu dihitung server dari jenis yang dipilih. Stok dicek dan
dikurangi pada baris jenis tiket di dalam transaksi database dengan `lockForUpdate()`, sehingga dua
pemesanan bersamaan tidak bisa membeli tiket yang sama. Membatalkan pesanan mengembalikan stok ke
jenis yang sama. Konser tanpa jenis tiket tidak bisa dipesan, dan jenis yang sudah dipakai pesanan
tidak bisa dihapus.

Cara mencoba:

1. Login admin, buka **Konser**, klik **Jenis Tiket** pada salah satu konser. Tambah/ubah jenis,
   lalu perhatikan angka "Sisa" pada daftar konser = total sisa semua jenis.
2. Buka `/konser/1/pesan`, pilih jenis tiket, isi nama/email/jumlah, kirim. Di `/pesanan` muncul
   kolom **Jenis Tiket** dan total sesuai harga jenis tersebut.
3. Coba kirim langsung lewat API:
   `POST /api/konser/1/pesanan` dengan body
   `{"nama_pemesan":"Budi","email":"budi@mail.test","jumlah":2,"ticket_type_id":1}`.
   Menambahkan `harga_satuan`, `total`, atau `status` ke body tidak mengubah hasil.
4. Kirim `jumlah` melebihi stok jenis → pesan "Sisa tiket VIP hanya …". Klik **Batal** pada pesanan
   → stok jenis itu bertambah kembali, jenis lain tidak berubah.
5. Coba hapus jenis yang sudah dipakai pesanan → pesan penolakan muncul.
6. Di React (`http://localhost:5173`), buka detail konser, pilih jenis tiket di form pesan, kirim.

## Fitur I — Pembatasan Pembelian

Satu pesanan maksimal **5 tiket**. Batas ini dibaca dari `config/tiketwar.php` dan diterapkan lewat
satu `PesananRequest` yang dipakai rute web dan API, jadi tidak ada angka yang duplikat di controller.
Jika jumlah melebihi batas, pesanan tidak dibuat dan stok tidak berubah.

Cara mencoba:

1. Buka `/konser/1/pesan`, isi jumlah **5** → pesanan dibuat. Ulangi dengan **6** → muncul
   "Maksimal 5 tiket per pesanan." dan `/pesanan` tidak bertambah.
2. Kirim `POST /api/konser/1/pesanan` dengan `jumlah: 6` → respons 422 berisi pesan yang sama.
3. Di React, kirim `jumlah: 6` pada detail konser → pesan validasi tampil di halaman.
4. `php artisan test` → 14 test lulus, termasuk uji batas web, API, dan perubahan nilai di config.

## Fitur G — Riwayat Pesanan

Setiap pesanan mendapat kode 12 karakter (huruf besar dan angka) secara otomatis. Kode tampil pada
pesan sukses React dan kolom **Kode Pesanan** di halaman admin `/pesanan`. Data pesanan lama
mendapat kode lewat migration. Kode tidak berubah saat status pesanan diubah.

Cara mencoba:

1. Buat pesanan lewat React (`http://localhost:5173`), lalu salin kode yang muncul pada pesan sukses.
2. Klik **Cek Pesanan** di header React, masukkan kode tersebut (huruf kecil juga diterima), lalu
   lihat konser, jenis tiket, jumlah, total, tanggal, dan status. Pesanan batal ditandai merah.
3. Cek lewat API: `GET /api/pesanan/cek/{kode}`. Kode yang salah menghasilkan 404 dengan pesan
   "Kode pesanan tidak ditemukan."
4. `php artisan test` → 18 test lulus, termasuk pembuatan kode unik dan pencarian via API.

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
