# Panduan Deployment — Website ICMI Orsat Kecamatan Caringin

Dokumen ini menjelaskan cara memasang website ke server produksi. Hosting dan domain resmi
**belum ditentukan**, sehingga panduan disiapkan untuk dua pilihan umum:

- **A. Shared hosting cPanel** — paling sederhana dan murah.
- **B. VPS Ubuntu + Nginx** — lebih fleksibel, memerlukan akses root.

Di seluruh dokumen, `domain-anda.or.id` adalah **contoh**. Ganti dengan domain resmi setelah tersedia.

---

## 1. Kebutuhan Server

| Komponen | Versi minimum | Catatan |
|---|---|---|
| PHP | 8.2 (disarankan 8.3/8.4) | Ekstensi: `bcmath`, `ctype`, `curl`, `dom`, `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`, `xml`, `zip` |
| MySQL / MariaDB | MySQL 8.0 / MariaDB 10.6 | Charset `utf8mb4` |
| Composer | 2.x | Bisa dijalankan di komputer lokal bila hosting tidak menyediakan |
| Node.js | 20+ | **Hanya** untuk membangun aset (`npm run build`); tidak dibutuhkan di server |
| Cron | wajib | Untuk publikasi terjadwal dan cadangan harian |
| HTTPS | wajib | Let's Encrypt / AutoSSL (gratis) |

Ekstensi `gd` dipakai untuk mengolah gambar unggahan; `zip` dipakai fitur cadangan.

## 2. Nilai `.env` Produksi

Salin `.env.example` menjadi `.env`, lalu sesuaikan minimal nilai berikut:

```dotenv
APP_NAME="ICMI Caringin"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.or.id
APP_TIMEZONE=Asia/Jakarta
APP_LOCALE=id

LOG_STACK=daily
LOG_LEVEL=warning

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database
DB_USERNAME=user_database
DB_PASSWORD=password_database_yang_kuat

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
CACHE_STORE=database
QUEUE_CONNECTION=database

ICMI_DEMO_MODE=false

# Minimal 12 karakter; seeder menolak password lemah di produksi.
SEED_ADMIN_PASSWORD=GantiDenganPasswordKuat!
SEED_WRITER_PASSWORD=GantiDenganPasswordLain!

BACKUP_KEEP=14
```

Penting:

- `APP_DEBUG` **wajib** `false` — bila `true`, detail error dan konfigurasi bisa terlihat publik.
- `APP_KEY` dibuat sekali dengan `php artisan key:generate`. Jangan diganti setelah website berjalan
  (sesi login akan hilang). Simpan salinannya di tempat aman.
- `ICMI_DEMO_MODE` harus `false` — konten contoh bukan data resmi.
- File `.env` tidak boleh ikut ke repositori dan tidak boleh bisa diakses dari browser.

## 3. Menyiapkan Kode dan Aset

Aset CSS/JS dibangun di komputer lokal (atau server yang memiliki Node.js):

```bash
git clone https://github.com/<organisasi>/ICMI-ORSAT.git icmi
cd icmi
composer install --no-dev --optimize-autoloader
npm ci
npm run build          # menghasilkan folder public/build
```

Folder `public/build` dan `vendor` harus ikut diunggah bila server tidak bisa menjalankan
`npm`/`composer`.

---

## A. Shared Hosting cPanel

### A.1 Unggah file

1. Buat database dan user MySQL melalui **MySQL® Databases**, beri user hak **ALL PRIVILEGES**.
2. Unggah seluruh proyek (termasuk `vendor` dan `public/build`) ke folder **di luar** `public_html`,
   misalnya `/home/USERCPANEL/icmi`. Cara termudah: kompres menjadi ZIP, unggah lewat
   **File Manager**, lalu *Extract*.
3. Buat file `/home/USERCPANEL/icmi/.env` sesuai bagian 2.

### A.2 Arahkan domain ke folder `public`

Hanya folder `public` yang boleh dapat diakses dari internet.

- **Addon domain / subdomain:** di **Domains**, ubah *Document Root* menjadi `icmi/public`.
- **Domain utama** (document root terkunci di `public_html`): pindahkan isi lama `public_html`
  ke folder cadangan, hapus folder `public_html` yang sudah kosong, lalu buat symlink melalui
  **Terminal** cPanel:

  ```bash
  ln -s /home/USERCPANEL/icmi/public /home/USERCPANEL/public_html
  ```

Jangan menyalin isi `public` ke `public_html` sambil membiarkan folder proyek di dalamnya —
itu berisiko membuka `.env` dan kode sumber ke publik.

### A.3 Pilih versi PHP

Di **MultiPHP Manager**, pilih PHP 8.2 atau lebih baru untuk domain tersebut. Di
**MultiPHP INI Editor** atur `upload_max_filesize = 8M`, `post_max_size = 12M`,
`memory_limit = 256M`.

### A.4 Inisialisasi (Terminal cPanel)

```bash
cd ~/icmi
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize
```

Bila `php` mengarah ke versi lama, gunakan path lengkap, misalnya
`/opt/cpanel/ea-php83/root/usr/bin/php`.

Bila Terminal tidak tersedia, jalankan perintah di atas sekali melalui **Cron Jobs**
(atur waktu satu menit ke depan, lalu hapus cron tersebut setelah berhasil).

### A.5 Cron Job

Di **Cron Jobs**, tambahkan entri *Once Per Minute* (`* * * * *`):

```bash
cd /home/USERCPANEL/icmi && php artisan schedule:run >> /dev/null 2>&1
```

Cron ini menjalankan publikasi berita terjadwal (tiap menit) dan cadangan harian (02.00 WIB).

### A.6 HTTPS

Aktifkan **SSL/TLS Status → Run AutoSSL**. Setelah sertifikat terpasang, pastikan
`APP_URL` memakai `https://`, lalu jalankan `php artisan optimize`.

---

## B. VPS Ubuntu 24.04 + Nginx

### B.1 Paket sistem

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install -y nginx mysql-server unzip git \
  php8.3-fpm php8.3-cli php8.3-mysql php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-gd php8.3-intl php8.3-bcmath
curl -sS https://getcomposer.org/installer | php && sudo mv composer.phar /usr/local/bin/composer
```

### B.2 Database

```bash
sudo mysql_secure_installation
sudo mysql -e "CREATE DATABASE icmi_caringin CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'icmi'@'localhost' IDENTIFIED BY 'password_database_yang_kuat';
GRANT ALL PRIVILEGES ON icmi_caringin.* TO 'icmi'@'localhost'; FLUSH PRIVILEGES;"
```

### B.3 Kode aplikasi

```bash
sudo mkdir -p /var/www/icmi && sudo chown $USER:www-data /var/www/icmi
git clone https://github.com/<organisasi>/ICMI-ORSAT.git /var/www/icmi
cd /var/www/icmi
composer install --no-dev --optimize-autoloader
cp .env.example .env && nano .env          # isi sesuai bagian 2
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
php artisan optimize
```

Aset `public/build` dibangun di lokal lalu diunggah (`scp -r public/build user@server:/var/www/icmi/public/`),
atau pasang Node.js di server dan jalankan `npm ci && npm run build`.

### B.4 Hak akses folder

```bash
sudo chown -R $USER:www-data /var/www/icmi
sudo find /var/www/icmi -type f -exec chmod 644 {} \;
sudo find /var/www/icmi -type d -exec chmod 755 {} \;
sudo chmod -R ug+rwx /var/www/icmi/storage /var/www/icmi/bootstrap/cache
chmod 640 /var/www/icmi/.env
```

### B.5 Konfigurasi Nginx

`/etc/nginx/sites-available/icmi`:

```nginx
server {
    listen 80;
    server_name domain-anda.or.id www.domain-anda.or.id;
    root /var/www/icmi/public;
    index index.php;

    client_max_body_size 12M;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~* \.(css|js|jpg|jpeg|png|webp|svg|woff2?)$ {
        expires 30d;
        access_log off;
        try_files $uri =404;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
sudo ln -s /etc/nginx/sites-available/icmi /etc/nginx/sites-enabled/
sudo rm -f /etc/nginx/sites-enabled/default
sudo nginx -t && sudo systemctl reload nginx
```

Atur juga `upload_max_filesize = 8M` dan `post_max_size = 12M` di
`/etc/php/8.3/fpm/php.ini`, lalu `sudo systemctl restart php8.3-fpm`.

### B.6 HTTPS (Let's Encrypt)

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d domain-anda.or.id -d www.domain-anda.or.id --redirect
```

Sertifikat diperpanjang otomatis oleh `certbot.timer`.

### B.7 Cron Scheduler

```bash
sudo crontab -u www-data -e
```

Tambahkan:

```cron
* * * * * cd /var/www/icmi && php artisan schedule:run >> /dev/null 2>&1
```

### B.8 Firewall

```bash
sudo ufw allow OpenSSH && sudo ufw allow 'Nginx Full' && sudo ufw enable
```

### B.9 Di belakang Cloudflare / load balancer

Bila website diakses melalui proxy (misalnya Cloudflare), tambahkan pengaturan *trusted proxies*
di `bootstrap/app.php` agar Laravel mengenali HTTPS dan IP pengunjung yang benar:

```php
$middleware->trustProxies(at: '*');
```

Hanya aktifkan ini bila server **benar-benar** berada di belakang proxy; jika tidak, header
`X-Forwarded-*` bisa dipalsukan pengunjung.

---

## 4. Daftar Periksa Setelah Deployment

- [ ] `https://domain-anda.or.id` terbuka dengan gembok HTTPS, dan `http://` dialihkan ke `https://`.
- [ ] Halaman error tidak menampilkan detail teknis (uji dengan membuka `/halaman-tidak-ada`).
- [ ] `https://domain-anda.or.id/.env` menghasilkan 404/403, **bukan** isi file.
- [ ] Login `/login` berhasil dengan akun Admin awal.
- [ ] **Segera ganti** password akun `admin@icmicaringin.or.id` dan `writer@icmicaringin.or.id`
      melalui CMS → Manajemen Pengguna; ganti email ke alamat resmi bila sudah ada.
- [ ] Isi **Pengaturan Website**: identitas, kontak resmi, media sosial (hanya data resmi — PRD §12).
- [ ] Isi **Profil Organisasi** dan **Struktur Pengurus** dengan data resmi.
- [ ] Unggah satu gambar uji di Media Library, pastikan tampil (artinya `storage:link` benar).
- [ ] Jalankan `php artisan schedule:list` — terlihat `articles:publish-scheduled` dan `icmi:backup`.
- [ ] Jalankan `php artisan icmi:backup` sekali dan pastikan file muncul di `storage/app/backups`.
- [ ] Buka `/sitemap.xml` dan `/robots.txt`, lalu daftarkan sitemap di
      [Google Search Console](https://search.google.com/search-console). Kode verifikasi GSC
      dan ID Google Analytics dapat diisi di Pengaturan Website.

## 5. Prosedur Pembaruan Aplikasi

```bash
cd /var/www/icmi                     # atau ~/icmi di cPanel
php artisan icmi:backup              # cadangkan dulu
php artisan down                     # tampilkan halaman pemeliharaan
git pull                             # atau unggah file versi baru
composer install --no-dev --optimize-autoloader
php artisan migrate --force
php artisan optimize:clear && php artisan optimize
php artisan up
```

Jangan lupa unggah ulang `public/build` bila ada perubahan tampilan.

Bila pembaruan bermasalah, kembalikan kode ke versi sebelumnya lalu pulihkan cadangan
(lihat [BACKUP.md](BACKUP.md)).

## 6. Pemecahan Masalah

| Gejala | Penyebab umum | Solusi |
|---|---|---|
| Error 500 putih | Hak akses `storage`/`bootstrap/cache` | Ulangi langkah B.4; lihat `storage/logs/laravel-*.log` |
| Gambar tidak tampil | `storage:link` belum dijalankan | `php artisan storage:link` |
| Tampilan tanpa gaya (CSS hilang) | `public/build` belum diunggah | Jalankan `npm run build`, unggah `public/build` |
| Berita terjadwal tidak terbit | Cron belum aktif | Periksa bagian A.5 / B.7 |
| Error 419 saat login | Sesi/HTTPS tidak cocok | Pastikan `APP_URL` dan `SESSION_SECURE_COOKIE` sesuai protokol |
| Perubahan `.env` tidak berpengaruh | Konfigurasi ter-cache | `php artisan optimize:clear && php artisan optimize` |
| Unggah gambar gagal | Batas PHP terlalu kecil | Naikkan `upload_max_filesize` dan `post_max_size` |
