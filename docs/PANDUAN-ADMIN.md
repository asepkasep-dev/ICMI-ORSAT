# Panduan Admin — Website ICMI Orsat Kecamatan Caringin

Panduan ini untuk pengurus yang memegang peran **Admin**. Admin mengelola seluruh isi website: berita, kategori, media, profil organisasi, struktur pengurus, akun pengguna, dan pengaturan website.

> Untuk penulis berita, lihat [PANDUAN-WRITER.md](PANDUAN-WRITER.md). Untuk teknisi server, lihat [DEPLOYMENT.md](DEPLOYMENT.md) dan [BACKUP.md](BACKUP.md).

---

## 1. Prinsip Utama

1. **Hanya data resmi.** Jangan mengisi nama pengurus, alamat, nomor kontak, sejarah, atau visi-misi dengan data perkiraan. Kosongkan bila data resmi belum tersedia — website otomatis menyembunyikan bagian tersebut atau menandainya "belum tersedia".
2. **Periksa sebelum menerbitkan.** Berita yang terbit langsung terlihat publik dan terindeks mesin pencari.
3. **Jaga akun.** Akun Admin dapat mengubah semua isi website. Jangan berbagi kata sandi.

---

## 2. Masuk dan Keluar

1. Buka `https://domain-anda.or.id/login`.
2. Masukkan email dan kata sandi, lalu klik **Masuk**.
3. Setelah berhasil, Anda diarahkan ke **Dashboard** CMS.
4. Untuk keluar, klik tombol **Keluar** di bagian bawah sidebar.

Catatan:
- Setelah **5 kali gagal** masuk, formulir dikunci sementara. Tunggu sekitar satu menit lalu coba lagi.
- Akun yang dinonaktifkan tidak dapat masuk.
- Tidak ada fitur "lupa kata sandi" otomatis. Kata sandi diatur ulang oleh Admin lain melalui **Manajemen Pengguna** (bagian 9).

### Mengganti kata sandi sendiri

1. Buka **Manajemen Pengguna**, klik **Edit** pada akun Anda.
2. Isi **Kata sandi** dan **Konfirmasi kata sandi** (minimal 8 karakter; disarankan 12 karakter atau lebih, campuran huruf, angka, dan simbol).
3. Klik **Simpan**. Kosongkan kolom kata sandi bila tidak ingin mengubahnya.

> **Wajib setelah instalasi:** segera ganti kata sandi bawaan akun Admin dan Writer.

---

## 3. Dashboard

Dashboard menampilkan ringkasan:
- Jumlah berita per status (total, terbit, draf, menunggu review, terjadwal).
- Pemberitahuan **"... berita menunggu review Anda"** bila ada berita yang diajukan Writer — klik **Review sekarang** untuk memeriksanya.
- **Aktivitas terbaru**: siapa melakukan apa (menerbitkan, mengunggah, menghapus, dan lain-lain).
- Progres pengembangan website.

Angka pada menu **Manajemen Berita** di sidebar menunjukkan jumlah berita yang menunggu review.

---

## 4. Manajemen Berita

### 4.1 Status berita

| Status | Arti | Tampil di website? |
|---|---|---|
| **Draf** | Masih ditulis | Tidak |
| **Menunggu Review** | Diajukan Writer untuk diperiksa Admin | Tidak |
| **Perlu Revisi** | Dikembalikan Admin dengan catatan | Tidak |
| **Terjadwal** | Akan terbit otomatis pada waktu yang ditentukan | Belum (sampai waktunya) |
| **Terbit** | Sudah tayang | Ya |
| **Diarsipkan** | Ditarik dari website | Tidak |

### 4.2 Mencari dan menyaring

Di halaman **Manajemen Berita**, gunakan kotak pencarian (judul/slug), filter **status**, dan filter **kategori**. Admin melihat berita semua penulis; Writer hanya melihat berita miliknya.

### 4.3 Menulis atau mengedit berita

Klik **Tulis Berita** atau ikon edit pada daftar. Isian formulir:

| Kolom | Keterangan |
|---|---|
| Judul | Wajib, 5–200 karakter |
| Slug URL | Alamat berita (`/berita/slug`). Otomatis dari judul; harus unik |
| Isi berita | Wajib. Toolbar menyediakan tebal, miring, heading, kutipan, daftar, dan tautan |
| Ringkasan | Opsional, maksimal 500 karakter. Tampil di kartu berita dan hasil pencarian |
| SEO title / description | Opsional. Bila kosong, judul dan ringkasan yang dipakai |
| Kategori | Wajib minimal satu |
| Gambar Utama | Unggah baru atau **Pilih dari Media** |
| Foto Pendukung | Beberapa foto sekaligus; masuk galeri publik setelah berita terbit |
| Berita unggulan | Khusus Admin. Tampil sebagai sorotan di beranda |

Format gambar: JPG, PNG, atau WebP, maksimal 2 MB per file dan 10 file per unggahan (batas dapat diubah teknisi). Gambar otomatis dioptimasi ke WebP bila server mendukung.

Klik **Simpan Draf / Simpan Perubahan** untuk menyimpan tanpa menerbitkan.

### 4.4 Meninjau berita Writer

1. Buka berita berstatus **Menunggu Review** (dari Dashboard atau filter status).
2. Periksa judul, isi, ejaan, kategori, gambar, dan kebenaran informasi.
3. Pilih salah satu:
   - **Terbitkan Sekarang** — berita langsung tayang.
   - **Jadwalkan** — pilih tanggal dan jam, lalu **Simpan Jadwal**. Waktu harus di masa depan.
   - **Minta Revisi** — tulis catatan yang jelas (minimal 5 karakter), lalu **Kembalikan untuk Revisi**. Writer akan melihat catatan ini di atas formulir beritanya.

Aksi yang sama juga tersedia dari menu aksi pada daftar berita.

### 4.5 Berita terjadwal

Berita **Terjadwal** diterbitkan otomatis oleh sistem paling lambat satu menit setelah waktunya tiba (cron server harus aktif — lihat DEPLOYMENT.md). Untuk mengubah waktu, buka berita dan jadwalkan ulang. Untuk menerbitkan lebih awal, klik **Terbitkan Sekarang**.

### 4.6 Memperbarui berita yang sudah terbit

Edit isi, lalu klik **Perbarui Publikasi**. Tanggal terbit asli tetap dipertahankan.

### 4.7 Mengarsipkan, memulihkan, dan menghapus

- **Arsipkan** (berita Terbit atau Terjadwal): berita ditarik dari website tetapi datanya tetap ada.
- **Pulihkan** (berita Diarsipkan): berita kembali menjadi **Draf**; terbitkan ulang bila perlu.
- **Hapus**: hanya Admin. Gunakan bila berita memang tidak diperlukan lagi; pilih **Arsipkan** bila ragu.

---

## 5. Kategori Berita

Menu **Kategori Berita** (khusus Admin):
- **Tambah kategori**: isi nama, slug (otomatis), dan deskripsi opsional.
- **Edit**: mengubah nama/slug/deskripsi. Mengubah slug mengubah alamat `/kategori/slug`.
- **Hapus**: bila kategori masih dipakai berita, Anda wajib memilih **kategori tujuan** — berita dipindahkan ke sana sebelum kategori dihapus.

---

## 6. Media Library

Tempat semua gambar website. Admin melihat seluruh media; Writer hanya media miliknya atau yang dipakai di beritanya.

- **Unggah**: pilih satu atau beberapa gambar sekaligus.
- **Detail**: klik gambar untuk mengisi **teks alternatif** (wajib — deskripsi gambar untuk tunanetra dan mesin pencari) dan **caption** (opsional, tampil di galeri). Panel detail juga menunjukkan di mana gambar dipakai.
- **Hapus**: gambar yang masih dipakai (gambar utama, foto pendukung, foto pengurus, foto ketua, atau logo) **tidak dapat dihapus**. Lepaskan dulu dari tempat pemakaiannya.

---

## 7. Profil Organisasi

Menu **Profil Organisasi** (khusus Admin) mengisi halaman `/profil-organisasi` dan bagian sambutan di beranda:

| Kolom | Keterangan |
|---|---|
| Sejarah | Teks berformat |
| Visi | Satu paragraf |
| Misi | Satu butir per baris |
| Tujuan | Satu butir per baris |
| Nama ketua, foto ketua, sambutan ketua | Ditampilkan di bagian sambutan |

Bagian yang dikosongkan tidak ditampilkan di website atau ditandai "belum tersedia". Klik **Simpan** setelah selesai.

---

## 8. Struktur Pengurus

Menu **Struktur Pengurus** (khusus Admin) mengisi halaman `/struktur-kepengurusan`.

- **Tambah pengurus**: nama, jabatan (wajib), bidang, keterangan singkat, urutan, foto, dan status tampil.
- **Bidang kosong** = **Pengurus Inti** (ketua, sekretaris, bendahara, dan sejenisnya). Pengurus dengan bidang yang sama dikelompokkan otomatis.
- **Urutan**: gunakan tombol naik/turun untuk menata urutan dalam satu kelompok.
- **Tampilkan/Sembunyikan**: menyembunyikan pengurus dari website tanpa menghapus datanya (misalnya saat pergantian pengurus).
- **Hapus**: menghapus data pengurus.

---

## 9. Manajemen Pengguna

Menu **Manajemen Pengguna** (khusus Admin):

- **Tambah akun**: nama, email, peran (**Admin** atau **Writer**), kata sandi (minimal 8 karakter) dan konfirmasinya.
- **Edit akun**: mengubah data, peran, atau mengatur ulang kata sandi (kosongkan bila tidak diubah).
- **Nonaktifkan/Aktifkan**: akun nonaktif tidak bisa masuk, tetapi berita dan riwayatnya tetap tersimpan. Gunakan ini untuk pengurus yang tidak lagi bertugas — jangan berbagi akun.

Pengaman otomatis:
- Anda tidak dapat menonaktifkan atau menurunkan peran akun Anda sendiri.
- Harus selalu ada minimal **satu Admin aktif**.

Saran: berikan peran **Admin** hanya kepada 1–2 pengurus yang bertanggung jawab atas website; pengurus lain cukup **Writer**.

---

## 10. Pengaturan Website

Menu **Pengaturan Website** (khusus Admin):

| Bagian | Isi |
|---|---|
| Identitas | Nama organisasi, nama singkat, nama lengkap, tagline, masa bakti, deskripsi website (dipakai mesin pencari) |
| Logo | Unggah logo resmi atau kembali ke logo bawaan |
| Kontak | Alamat sekretariat, email, nomor kontak — tampil di halaman Kontak dan footer |
| Media sosial | Alamat lengkap diawali `https://` (Instagram, Facebook, YouTube, TikTok) |
| SEO | Kode verifikasi Google Search Console dan ID Google Analytics 4 |

Kolom kontak dan media sosial yang kosong ditampilkan sebagai "belum tersedia" — jangan diisi data sementara.

### Google Search Console

1. Di Search Console, tambahkan properti **Awalan URL** `https://domain-anda.or.id`.
2. Pilih metode verifikasi **Tag HTML**. Salin **hanya nilai `content`** dari tag `<meta name="google-site-verification" content="NILAI_INI">`.
3. Tempel nilai tersebut di kolom verifikasi, **Simpan**, lalu klik **Verifikasi** di Search Console.
4. Kirim sitemap: `https://domain-anda.or.id/sitemap.xml`.

### Google Analytics 4 (opsional)

Isi **ID Pengukuran** berformat `G-XXXXXXXXXX`. Kosongkan bila tidak memakai analitik.

---

## 11. Cadangan Data

Website membuat cadangan otomatis setiap hari pukul 02.00 (bila cron server aktif). Pemulihan dilakukan oleh teknisi melalui server — lihat [BACKUP.md](BACKUP.md). Sebagai Admin, pastikan teknisi menyimpan salinan cadangan di luar server secara berkala.

---

## 12. Tips Keamanan

- Gunakan kata sandi unik dan panjang; jangan memakai kata sandi yang sama dengan email atau media sosial.
- Selalu **Keluar** setelah memakai komputer bersama.
- Nonaktifkan akun pengurus yang sudah tidak bertugas.
- Periksa **Aktivitas terbaru** di Dashboard secara berkala untuk tindakan yang tidak dikenal.
- Jangan mengunggah dokumen pribadi (KTP, data anggota) sebagai gambar berita.

---

## 13. Daftar Periksa Rutin

**Mingguan**
- [ ] Tinjau berita yang menunggu review.
- [ ] Periksa aktivitas terbaru.

**Bulanan**
- [ ] Pastikan data pengurus dan kontak masih benar.
- [ ] Konfirmasi ke teknisi bahwa cadangan berjalan dan salinan luar server tersedia.

**Saat pergantian pengurus**
- [ ] Perbarui Struktur Pengurus dan Profil Organisasi (ketua, sambutan, masa bakti di Pengaturan Website).
- [ ] Nonaktifkan akun pengurus lama, buat akun pengurus baru.
