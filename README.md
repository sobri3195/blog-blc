# Brainy Journal

CMS blog berita pendidikan berbahasa Indonesia berbasis PHP native, PDO, server-side rendering, dan MySQL/MariaDB. Desain dan konten contoh dibuat orisinal; tidak ada aset atau klaim dari situs referensi yang disalin.

## Persyaratan

- PHP **8.2+** dengan ekstensi PDO MySQL, mbstring, fileinfo, dan GD/getimagesize.
- MySQL 8.0+ atau MariaDB 10.6+; Composer hanya diperlukan saat instalasi/deployment.
- Document root **wajib** menunjuk ke `public/`.

Dependensi produksi tunggal adalah `ezyang/htmlpurifier`, sanitizer allowlist HTML artikel yang terawat. Tidak dibutuhkan Node.js.

## Instalasi lokal

```bash
cp .env.example .env
composer install --no-dev --optimize-autoloader
mysql -u root -p -e 'CREATE DATABASE brainy_journal CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci'
mysql -u root -p brainy_journal < database/schema.sql
# Atur DB_* dan APP_URL di .env, lalu:
php scripts/create-admin.php
mysql -u root -p brainy_journal < database/seed.sql
php -S localhost:8000 -t public public/index.php
```

Seed tidak berisi kata sandi. Enam draf contoh baru dibuat bila admin sudah tersedia. Login melalui `/admin/login`.

## Shared hosting / Apache

Letakkan repository di luar `public_html`, misalnya `~/brainy-journal`, dan arahkan document root domain/subdomain ke `~/brainy-journal/public`. Jika panel hosting tidak mendukung document root khusus, buat symlink aman dari `public_html` ke isi `public`; **jangan** menyalin `.env`, `config`, `database`, `storage`, atau `vendor` ke area publik. Apache memakai `public/.htaccess`; uploads memiliki aturan tambahan agar PHP tidak dieksekusi. Beri izin tulis hanya pada `storage/logs` dan `public/uploads`.

Contoh virtual host Nginx:

```nginx
server {
  root /var/www/brainy-journal/public;
  index index.php;
  location / { try_files $uri $uri/ /index.php?$query_string; }
  location ~ \.php$ { include fastcgi_params; fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name; fastcgi_pass unix:/run/php/php8.2-fpm.sock; }
  location ^~ /uploads/ { location ~ \.php { deny all; } try_files $uri =404; }
  location ~ /\. { deny all; }
}
```

Pada produksi gunakan HTTPS, `APP_ENV=production`, URL kanonis yang benar, kredensial DB dengan hak minimum, serta cron/rotasi log dari panel hosting. Waktu disimpan UTC dan ditampilkan memakai `APP_TIMEZONE`.

## Operasi dan backup

```bash
# backup konsisten
mysqldump --single-transaction --routines --triggers -u USER -p DB > backup.sql
tar -czf media-backup.tar.gz public/uploads
# restore ke database kosong dan direktori aplikasi
mysql -u USER -p DB < backup.sql
tar -xzf media-backup.tar.gz
```

Backup `.env` secara terpisah di penyimpanan terenkripsi, uji restore berkala, dan jangan memasukkannya ke Git. Sebelum upgrade, backup database dan media; jalankan `composer install --no-dev --optimize-autoloader` setelah upload release.

## Fitur dan keamanan

- Beranda, arsip/paginasi, kategori, tag, pencarian, detail, halaman informasi, 404, robots, sitemap, SEO/OG/JSON-LD, artikel terkait, bagikan WhatsApp/salin tautan.
- Admin/penulis dengan pemeriksaan role dan kepemilikan; penulis hanya draf/pending, admin dapat terbit/arsip; preview privat dan redirect slug lama 301.
- CRUD inti artikel, kategori/tag, media, halaman, pengaturan, dan pembuatan pengguna. Penghapusan kategori/media sengaja tidak ditawarkan saat masih direferensikan (foreign key `RESTRICT`).
- Prepared statements, CSRF, cookie sesi aman, generic login error, rate limit tersimpan, sanitasi HTML, validasi MIME/dimensi upload dan nama acak.

Editor v1 menerima HTML terkontrol (heading, daftar, kutipan, tabel, tautan, dan gambar); HTMLPurifier membersihkannya saat simpan. Pilih media dari pengelola lalu hubungkan melalui database/admin lanjutan bila dibutuhkan—unggahan media sendiri sudah berfungsi dan tervalidasi.

## Verifikasi

`php tests/run.php` memeriksa slug, CSRF, hashing, dan sanitasi berbahaya tanpa memerlukan data fixture. Pengujian integrasi database memerlukan MySQL/MariaDB aktif dan harus dijalankan pada staging: buat penulis kedua, submit draf, coba status `published` lewat request langsung (harus ditolak), uji kepemilikan, terbitkan sebagai admin, lalu periksa beranda/kategori/cari/sitemap dan redirect lama. Uji upload SVG/PHP serta file oversized (harus ditolak). Periksa manual viewport 360, 768, dan 1440 px sebelum go-live.
