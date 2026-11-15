# Website ICMI Orsat Kecamatan Caringin

Website profil dan berita organisasi dengan CMS untuk pengurus **ICMI Orsat Kecamatan Caringin, Kabupaten Bogor**. Antarmuka berbahasa Indonesia, responsif untuk ponsel dan desktop, dengan peran **Admin** dan **Writer**.

> Hanya gunakan data resmi organisasi. Profil, pengurus, kontak, dan media sosial yang belum tersedia tidak boleh diisi data rekaan. Mode demo hanya untuk pengembangan, bukan produksi.

## Fitur

- **Website publik:** beranda, profil organisasi, struktur kepengurusan, berita, halaman kategori, pencarian, galeri foto, dan kontak.
- **CMS berita:** editor teks berformat, gambar utama, foto pendukung, kategori, ringkasan, SEO, serta berita unggulan.
- **Alur publikasi:** Draf → Menunggu Review → Perlu Revisi / Terbit / Terjadwal; arsip dan pemulihan ke draf oleh Admin.
- **Hak akses:** Admin mengelola seluruh website; Writer menulis berita sendiri, mengajukan review, dan dapat menerbitkan sesuai kesepakatan redaksi. Writer hanya dapat mengedit isi berstatus Draf atau Perlu Revisi.
- **Organisasi:** sejarah, visi-misi, tujuan, sambutan ketua, pengurus per bidang, identitas, logo, kontak, dan media sosial — dikelola Admin.
- **Media Library:** unggahan JPG/PNG/WebP, optimasi gambar, teks alternatif, caption, dan perlindungan penghapusan media yang masih digunakan.
- **SEO:** metadata, Open Graph, JSON-LD, sitemap dan robots.txt; verifikasi Search Console dan Google Analytics 4 opsional.
- **Operasional:** log aktivitas, pembatasan percobaan login, sanitasi konten, header keamanan, publikasi terjadwal, cadangan harian, dan pemulihan.

## Teknologi dan Kebutuhan

| Komponen | Keterangan |
|---|---|
| Backend | Laravel 12, PHP **8.3+** (dependensi pengolahan gambar memerlukan 8.3; lokal memakai 8.4) |
| Interaksi | Livewire 3, Alpine.js |
| Tampilan | Tailwind CSS 4, Blade, Vite 7 |
| Editor | Trix |
| Database | SQLite untuk pengembangan lokal; MySQL 8 / MariaDB 10.6 untuk produksi |
| Dependensi | Composer 2; Node.js **22.12+** untuk membangun aset |

Ekstensi PHP: `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `mbstring`, `openssl`, `pdo`, `tokenizer`, `xml`, `zip`; `pdo_sqlite` untuk SQLite atau `pdo_mysql` untuk MySQL. Kebutuhan server selengkapnya ada di [DEPLOYMENT.md](docs/DEPLOYMENT.md).

## Instalasi Lokal

Contoh perintah berikut dijalankan dari folder proyek dengan Bash / Git Bash. Pastikan `php`, `composer`, dan `npm` tersedia di PATH. Langkah ini untuk **instalasi baru**; jangan menimpa `.env` atau database yang sudah dipakai.

### 1. Pasang dependensi

```bash
composer install
npm install
```

### 2. Konfigurasi lingkungan

Bila belum ada `.env`, salin `.env.example`:

```bash
cp .env.example .env
```

Contoh konfigurasi **lokal**:

```dotenv
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id
DB_CONNECTION=sqlite
ICMI_DEMO_MODE=false
```

Hapus baris `DB_DATABASE` dari `.env` agar SQLite memakai `database/database.sqlite`. Alternatifnya, isi `DB_DATABASE` dengan path absolut file SQLite. Nilai host/port/user MySQL tidak digunakan oleh driver SQLite.

### 3. Siapkan database dan media

```bash
php -r "file_exists('database/database.sqlite') || touch('database/database.sqlite');"
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

Seeder menyiapkan akun awal dan kategori. Konten contoh hanya dibuat bila `ICMI_DEMO_MODE=true`; biarkan `false` untuk mengisi website dengan data resmi melalui CMS.

### 4. Jalankan aplikasi

Pilihan sederhana — dua terminal:

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

Atau jalankan `composer run dev` untuk server aplikasi, Vite, pendengar antrean, dan log sekaligus. Bila lingkungan tidak mendukung Laravel Pail, gunakan dua terminal di atas.

Buka **http://localhost:8000**; login CMS di **http://localhost:8000/login**.

Untuk menguji publikasi terjadwal dan cadangan otomatis di lokal, jalankan di terminal terpisah:

```bash
php artisan schedule:work
```

### Akun awal (khusus lokal)

| Peran | Email | Kata sandi bawaan |
|---|---|---|
| Admin | `admin@icmicaringin.or.id` | `password` |
| Writer | `writer@icmicaringin.or.id` | `password` |

Kata sandi seeder dapat diubah melalui `SEED_ADMIN_PASSWORD` dan `SEED_WRITER_PASSWORD` di `.env` **sebelum** seeding. Jangan gunakan akun/kata sandi bawaan di produksi: seeder produksi menolak `password` atau kata sandi kurang dari 12 karakter. Setelah instalasi, sesuaikan email dan kata sandi lewat Manajemen Pengguna.

## Pengujian dan Build

```bash
php artisan test
npm run build
```

Tes otomatis mencakup publikasi dan hak akses, pengelolaan konten dan organisasi, media, SEO, keamanan, serta cadangan/pemulihan. Pengujian lokal menggunakan SQLite; pemulihan MySQL/MariaDB dan pemindahan lintas database **belum diverifikasi** di lingkungan ini dan wajib diuji sebelum digunakan di produksi.

`npm run build` menghasilkan aset produksi di `public/build`. Jangan jalankan Vite dev server di produksi.

## Perintah Operasional

| Perintah | Fungsi |
|---|---|
| `php artisan articles:publish-scheduled` | Menerbitkan berita terjadwal yang sudah jatuh tempo |
| `php artisan icmi:backup` | Membuat cadangan data dan media |
| `php artisan icmi:restore` | Menampilkan daftar cadangan yang tersedia |
| `php artisan icmi:restore <nama-file.zip>` | Memulihkan cadangan — **mengganti data/media saat ini** dan mengeluarkan semua sesi login |
| `php artisan schedule:list` | Memeriksa jadwal publikasi dan cadangan |

Scheduler menjalankan publikasi **tiap menit** dan cadangan **pukul 02.00 WIB** (dengan `APP_TIMEZONE=Asia/Jakarta`). Produksi memerlukan cron `schedule:run` tiap menit. Cadangan disimpan di `storage/app/backups`, dengan retensi 14 file terbaru (`BACKUP_PATH` dan `BACKUP_KEEP` dapat diatur).

Cadangan tidak memuat kode aplikasi atau `.env`. Simpan salinan `.env` secara aman dan cadangan di luar server. Baca [BACKUP.md](docs/BACKUP.md) sebelum melakukan pemulihan.

## Dokumentasi

- [Panduan Admin](docs/PANDUAN-ADMIN.md) — berita, review, organisasi, pengguna, dan pengaturan.
- [Panduan Writer](docs/PANDUAN-WRITER.md) — penulisan berita, gambar, review, dan revisi.
- [Deployment](docs/DEPLOYMENT.md) — cPanel atau VPS, HTTPS, cron, dan pemeriksaan produksi.
- [Cadangan dan pemulihan](docs/BACKUP.md) — retensi, salinan luar server, pemulihan, dan uji berkala.

## Status Milestone

| No. | Milestone | Status |
|---|---|---|
| 1 | Project Foundation | Selesai |
| 2 | UI/UX Implementation | Selesai |
| 3 | Content Management System | Selesai |
| 4 | Organization Profile | Selesai |
| 5 | Public Website & SEO | Selesai |
| 6 | Testing & Deployment | Kesiapan teknis dan dokumentasi selesai; deployment langsung menunggu hosting/domain |

Dashboard menandai Milestone 6 selesai untuk pekerjaan teknis dan dokumentasi. **Website belum dipasang pada hosting/domain resmi.** Pengujian responsif lokal telah mencakup lebar 320, 360, 375, dan 768 piksel. Panduan pelatihan Admin/Writer tersedia; pelatihan langsung dan pemeriksaan akhir server dilakukan saat serah terima.

### Sebelum go-live

- [ ] Tentukan hosting dan domain resmi, lalu ikuti DEPLOYMENT.md.
- [ ] Pasang HTTPS, cron, dan akun produksi dengan kredensial kuat.
- [ ] Isi data resmi organisasi, pengurus, kontak, dan berita.
- [ ] Uji publikasi, unggahan, backup, dan restore pada database/server produksi.
- [ ] Siapkan salinan cadangan di luar server serta penanggung jawab operasional.
- [ ] Latih Admin/Writer dengan panduan dan periksa hasilnya bersama pengurus.

## Keamanan

Jangan commit `.env`, kredensial, atau arsip cadangan. Domain harus mengarah hanya ke folder `public`, dan `APP_DEBUG=false` di produksi. Laporkan masalah keamanan secara privat kepada pengelola proyek tanpa menyertakan kata sandi atau data pengguna di laporan publik.
