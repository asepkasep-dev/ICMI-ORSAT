# Panduan Cadangan & Pemulihan — Website ICMI Orsat Kecamatan Caringin

Dokumen ini untuk pengelola teknis (Admin server). Perintah dijalankan dari folder aplikasi,
misalnya `/var/www/icmi` (VPS) atau `~/icmi` (cPanel). Lihat juga [DEPLOYMENT.md](DEPLOYMENT.md).

---

## 1. Ringkasan

| Hal | Keterangan |
|---|---|
| Perintah cadangan | `php artisan icmi:backup` |
| Perintah pemulihan | `php artisan icmi:restore <nama-file>` |
| Jadwal otomatis | Setiap hari pukul **02.00 WIB** (memerlukan cron `schedule:run`) |
| Lokasi file | `storage/app/backups/` (dapat diubah dengan `BACKUP_PATH`) |
| Nama file | `icmi-backup-TAHUNBULANTANGGAL-JAMMENITDETIK-ms.zip` |
| Retensi | 14 cadangan terbaru (dapat diubah dengan `BACKUP_KEEP`) |

## 2. Isi Cadangan

Setiap cadangan adalah **satu file ZIP** yang berisi:

- **Seluruh data website** — berita, kategori, media library, profil organisasi, struktur
  pengurus, pengaturan website, akun pengguna, dan log aktivitas (`database/*.json`).
- **Seluruh file media** yang diunggah — gambar berita, foto pengurus, logo (`media/`).
- **Manifest** (`manifest.json`) — waktu pembuatan, asal website, daftar migrasi, dan jumlah data.

Yang **tidak** ikut dicadangkan (dan memang tidak perlu):

- Sesi login, cache, antrean, dan token reset password — data sementara.
- Kode aplikasi — tersimpan di repositori Git.
- File `.env` — **simpan salinannya sendiri** di tempat aman (lihat bagian 7).

> **Penting:** file cadangan memuat hash password pengguna dan data internal. Perlakukan
> seperti dokumen rahasia. Folder `storage/` tidak dapat diakses dari internet selama domain
> diarahkan ke folder `public` sesuai DEPLOYMENT.md.

## 3. Cadangan Otomatis

Cadangan harian berjalan otomatis bila cron scheduler sudah dipasang
(DEPLOYMENT.md bagian A.5 / B.7). Untuk memastikan:

```bash
php artisan schedule:list          # harus tampil: icmi:backup ... 02:00
php artisan icmi:restore           # tanpa argumen = daftar cadangan yang ada
```

Bila tidak ada cadangan baru setiap pagi, periksa cron dan log `storage/logs/`.

### Mengatur retensi dan lokasi

Di `.env`:

```dotenv
BACKUP_KEEP=14                       # jumlah cadangan terbaru yang disimpan
BACKUP_PATH=/home/user/cadangan-icmi # opsional; kosongkan untuk storage/app/backups
```

Setiap cadangan berukuran kurang lebih sebesar total file media. Dengan `BACKUP_KEEP=14`,
siapkan ruang disk sekitar 14 × ukuran media. Kurangi nilainya bila kuota hosting terbatas.
Setelah mengubah `.env`, jalankan `php artisan optimize`.

`BACKUP_PATH` harus berada **di luar** folder `public` / `public_html`.

## 4. Cadangan Manual

Jalankan sebelum perubahan besar (pembaruan aplikasi, impor data, penghapusan massal):

```bash
php artisan icmi:backup
```

Contoh keluaran:

```
Cadangan dibuat: /var/www/icmi/storage/app/backups/icmi-backup-20261116-140512-118.zip
1245 baris data dari 10 tabel, 312 file media, 84.6 MB.
```

Opsi `--keep=N` menimpa retensi untuk sekali jalan, misalnya `php artisan icmi:backup --keep=30`.

## 5. Menyimpan Salinan di Luar Server (Wajib)

Cadangan yang hanya ada di server yang sama **tidak melindungi** dari server rusak, akun
hosting ditangguhkan, atau peretasan. Minimal **seminggu sekali**, unduh cadangan terbaru ke
lokasi lain, misalnya Google Drive organisasi atau hard disk eksternal.

- **cPanel:** File Manager → buka folder `icmi/storage/app/backups` → klik kanan file → *Download*.
- **VPS (dari komputer pengelola):**

  ```bash
  scp user@server:/var/www/icmi/storage/app/backups/icmi-backup-*.zip ./cadangan-icmi/
  # atau sinkronkan seluruh folder:
  rsync -av user@server:/var/www/icmi/storage/app/backups/ ./cadangan-icmi/
  ```

Simpan salinan `.env` (terutama `APP_KEY` dan kredensial database) secara terpisah dari
file cadangan, misalnya di pengelola password organisasi.

Bila hosting menyediakan fitur cadangan bawaan (misalnya *JetBackup* di cPanel), aktifkan juga
sebagai lapisan perlindungan tambahan.

## 6. Pemulihan

### 6.1 Yang terjadi saat pemulihan

1. File cadangan diperiksa: harus cadangan website ICMI yang valid, dan tidak berasal dari
   versi aplikasi yang lebih baru.
2. Ringkasan cadangan ditampilkan dan Anda diminta konfirmasi.
3. Dibuat **cadangan pengaman** kondisi saat ini (`...-pra-pemulihan.zip`).
4. Website otomatis masuk **mode pemeliharaan** (pengunjung melihat halaman
   "Sedang Dalam Pemeliharaan").
5. File media diekstrak dan diperiksa lebih dulu. Bila arsip rusak, proses berhenti
   **sebelum** data apa pun diubah.
6. Seluruh data dan file media **diganti** dengan isi cadangan.
7. Semua sesi login dihapus — **semua pengguna harus masuk kembali**.
8. Website kembali online (kecuali sebelumnya memang sedang dalam mode pemeliharaan).

> Pemulihan **mengganti seluruh isi** website ke kondisi saat cadangan dibuat. Berita atau
> perubahan yang dibuat setelah waktu cadangan akan hilang (tetapi masih ada di cadangan
> pengaman `-pra-pemulihan`).

Penggantian database dan file media bukan satu transaksi bersama. Bila penyalinan media
gagal setelah database dipulihkan, hasil dapat tidak lengkap. Hentikan akses dengan
`php artisan down`, perbaiki penyebabnya, lalu ulangi pemulihan atau gunakan cadangan pengaman.
Periksa hasil sebelum menjalankan `php artisan up`.

### 6.2 Memulihkan di server yang sama

```bash
php artisan icmi:restore                                   # lihat daftar cadangan
php artisan icmi:restore icmi-backup-20261116-020000-412.zip
```

Ketik `yes` saat diminta konfirmasi. Argumen bisa berupa nama file di folder cadangan atau
path lengkap ke file ZIP di lokasi lain.

Opsi tambahan:

| Opsi | Fungsi |
|---|---|
| `--force` | Lewati konfirmasi (untuk skrip). Gunakan dengan hati-hati. |
| `--no-safety-backup` | Jangan buat cadangan pengaman. Hanya bila ruang disk tidak cukup. |

### 6.3 Memulihkan ke server baru (pindah hosting / server rusak)

1. Pasang aplikasi di server baru mengikuti DEPLOYMENT.md sampai langkah
   `php artisan migrate --force` dan `php artisan storage:link`.
   Gunakan **versi kode yang sama atau lebih baru** dari saat cadangan dibuat.
2. Unggah file cadangan ke `storage/app/backups/` di server baru.
3. Jalankan:

   ```bash
   php artisan icmi:restore icmi-backup-20261116-020000-412.zip
   php artisan optimize
   ```

4. Login dengan akun yang ada **di dalam cadangan** (password lama), bukan akun dari seeder.
5. Lakukan pemeriksaan bagian 6.5.

### 6.4 Membatalkan pemulihan

Bila hasil pemulihan ternyata salah, pulihkan kembali dari cadangan pengaman:

```bash
php artisan icmi:restore icmi-backup-20261116-140733-905-pra-pemulihan.zip --no-safety-backup
```

### 6.5 Pemeriksaan setelah pemulihan

- [ ] Beranda, daftar berita, dan detail berita tampil normal dengan gambar.
- [ ] Profil Organisasi dan Struktur Kepengurusan sesuai.
- [ ] Login CMS berhasil; jumlah berita di dashboard sesuai perkiraan.
- [ ] Pengaturan Website (logo, kontak, media sosial) sesuai.

## 7. Uji Pemulihan Berkala

Cadangan yang belum pernah diuji belum tentu bisa dipulihkan. Minimal **tiga bulan sekali**,
uji pemulihan di komputer lokal atau server percobaan (bukan server produksi):

1. Unduh cadangan terbaru dari server.
2. Di salinan aplikasi lokal: `php artisan migrate`, lalu
   `php artisan icmi:restore /path/ke/icmi-backup-....zip`.
3. Buka website lokal dan lakukan pemeriksaan bagian 6.5.

Data disimpan dalam format JSON sehingga cadangan dirancang dapat dipulihkan lintas jenis
database (misalnya dari MySQL produksi ke SQLite lokal). Pengujian saat ini baru dilakukan
pada SQLite; pemulihan MySQL/MariaDB dan lintas database belum diverifikasi. Uji pada salinan
database produksi sebelum mengandalkan pemulihan tersebut.

## 8. Pemecahan Masalah

| Pesan / gejala | Penyebab | Solusi |
|---|---|---|
| `Cadangan dibuat oleh versi aplikasi yang lebih baru` | Kode di server lebih lama dari cadangan | Perbarui kode (`git pull`), jalankan `php artisan migrate --force`, ulangi |
| Peringatan `Cadangan berasal dari versi lebih lama` | Ada migrasi baru sejak cadangan dibuat | Aman dilanjutkan; kolom baru memakai nilai bawaan |
| `File ini bukan cadangan website ICMI yang valid` / `rusak` | File terpotong saat diunduh/diunggah, atau bukan file cadangan | Unggah ulang file; bandingkan ukuran file dengan aslinya |
| `Nama file tidak aman di dalam cadangan` | Arsip telah dimodifikasi | Jangan gunakan file tersebut; pakai cadangan lain |
| `File cadangan tidak ditemukan` | Nama/path salah | Jalankan `php artisan icmi:restore` tanpa argumen untuk melihat daftar |
| Tidak ada cadangan harian baru | Cron tidak berjalan | Periksa cron `schedule:run` (DEPLOYMENT.md A.5/B.7) |
| `Pencadangan gagal` | Disk penuh atau folder tidak dapat ditulis | Kurangi `BACKUP_KEEP`, hapus cadangan lama, periksa hak akses `storage/` |
| Website tetap "Sedang Dalam Pemeliharaan" | Proses terhenti paksa di tengah pemulihan | Jalankan `php artisan up` |
| Gambar tidak tampil setelah pindah server | `storage:link` belum dijalankan | `php artisan storage:link` |
