# Operasional dan Persiapan Rilis

Panduan Tahap 9, 29 Oktober 2026. **Belum ada deployment yang dilakukan.** Perintah di sini dijalankan operator pada lingkungan yang dituju setelah konfigurasi domain, server, kredensial, backup dan akses diverifikasi.

## Komponen runtime

Laravel/PHP, MySQL, web server dengan document root `public/`, database queue worker, Reverb dan asset Vite. Gunakan versi PHP/ekstensi sesuai `composer.lock` serta `composer check-platform-reqs --no-dev`. PHP CLI worker dan PHP web perlu ekstensi/font yang sesuai untuk DomPDF. Composer dapat memerlukan ekstensi zip atau alat unzip untuk instalasi paket. Node/npm hanya diperlukan saat membangun asset.

Jangan menyajikan root repository, `.env`, vendor, storage private atau dump SQL melalui web. Hanya `storage/app/public` yang boleh ditautkan ke public/storage. Bukti pembayaran/piagam berada pada disk private dan diakses melalui controller berotorisasi. `storage` dan `bootstrap/cache` perlu writable oleh runtime dengan izin minimum; hindari permission terbuka ke semua pengguna.

## Konfigurasi

Atur pada secret store atau `.env` yang tidak di-commit:

- APP_ENV=production, APP_DEBUG=false, APP_URL domain HTTPS resmi, timezone Asia/Jakarta, locale id. Pertahankan APP_KEY lama pada pembaruan; jangan menjalankan key:generate pada instalasi yang sudah menyimpan data terenkripsi/session.
- MySQL database aplikasi dengan akun khusus, berbeda dari database/akun tes. QUEUE_CONNECTION=database, CACHE_STORE=database, SESSION_DRIVER=database. Jangan memakai array/sync untuk runtime produksi.
- SESSION_SECURE_COOKIE=true pada HTTPS, HTTP_ONLY=true dan SAME_SITE=lax. Konfigurasi trusted proxy hanya untuk proxy yang benar-benar digunakan; jangan mengizinkan semua IP tanpa memahami topologi.
- PAYMENT_BANK_NAME/ACCOUNT_NUMBER/ACCOUNT_NAME/INSTRUCTIONS dengan informasi resmi panitia. Tidak ada payment gateway.
- Mailer pengiriman nyata untuk reset password. MAIL_MAILER=log hanya berguna secara lokal dan tidak mengirim email kepada pengguna; notifikasi lomba tambahan tetap di luar MVP.
- BROADCAST_CONNECTION=reverb; REVERB_APP_ID/KEY/SECRET unik, host/port/scheme yang benar. Secret hanya server-side; VITE_REVERB_APP_KEY adalah kunci publik, jangan menyalin APP_SECRET ke variabel VITE.
- REVERB_ALLOWED_ORIGINS berisi host yang diizinkan secara eksplisit. REVERB_SCALING_ENABLED tetap false untuk satu instance tanpa Redis. Client events dinonaktifkan.

`php artisan reverb:install` dapat menyiapkan kredensial pada instalasi baru, bukan perintah rotasi rutin saat setiap rilis. Pada production, gunakan nama host/secret dari konfigurasi operator, lalu build asset dengan VITE_REVERB_* yang selaras.

## Proses rilis

1. Jalankan suite pada database tes terpisah dan build asset. Siapkan backup yang dapat dipulihkan.
2. Install dependency dari lockfile pada release yang disiapkan:

```text
composer install --no-dev --optimize-autoloader --no-interaction
composer check-platform-reqs --no-dev
npm ci
npm run build
```

3. Tempatkan konfigurasi dan storage persisten, periksa SQL migrasi dengan `php artisan migrate --pretend`, lalu terapkan `php artisan migrate --force`. Hindari migrate:fresh, db:wipe, seed demo atau perubahan migration yang sudah dipakai.
4. `php artisan storage:link` hanya untuk public disk, lalu `php artisan optimize`. Pastikan hasil cache tidak mengandung konfigurasi tes/development. Reload PHP/proses sesuai platform.
5. Restart worker dan Reverb agar memakai kode/config baru. `php artisan queue:restart` dan `php artisan reverb:restart` memberi sinyal keluar; process manager harus menyalakan kembali proses. Verifikasi proses tidak berhenti permanen.
6. Smoke test landing/event/login, akses peran/assignment, file privat, progres HTTP, private channel, satu transaksi pengujian yang disetujui operator, hasil FINAL/PDF dan queue failures. Jangan melakukan pembayaran/finalisasi data lomba asli sebagai smoke test.

Dokumen ini tidak menetapkan domain, penyedia hosting atau membuka jaringan secara otomatis. Akun Owner pertama harus diprovisikan operator melalui console administrasi tepercaya dengan kata sandi kuat dan peran OWNER, bukan melalui registrasi publik. Seeder bawaan memang melewati production, tetapi bukan prosedur provisioning production. Akun demo default tidak boleh dibawa ke layanan publik.

## Worker, Reverb dan HTTPS

Proses yang dipelihara process manager:

```text
php artisan queue:work database --tries=3 --timeout=60
php artisan reverb:start --host=127.0.0.1 --port=8080
```

Database queue retry_after default 90 detik harus lebih besar dari timeout worker/job 60 detik. Jalankan kembali proses setelah crash; arahkan log ke penyimpanan terbatas dan pantau kegagalan. Jangan menjalankan banyak worker tanpa mempertimbangkan kapasitas MySQL/PDF. Tidak ada scheduled job khusus aplikasi yang saat ini wajib dijalankan.

Terminasi TLS pada reverse proxy sesuai hosting; teruskan WebSocket Upgrade untuk `/app` dan request signed server API `/apps` ke Reverb internal. Jangan membuka port internal ke semua jaringan tanpa kebutuhan. URL browser menggunakan wss/HTTPS sesuai VITE config. Karena environment build ditanam dalam asset, perubahan host/key publik membutuhkan rebuild. Uji origin asing ditolak, private auth memakai session/CSRF, dan fallback berjalan saat Reverb dimatikan. Konfigurasi reverse proxy/TLS nyata belum divalidasi pada sesi ini.

## Monitoring dan recovery

- `/up` adalah health route Laravel, bukan bukti bahwa DB, worker, Reverb atau PDF sehat. Pantau layanan dan queue lag/failed jobs terpisah.
- `php artisan queue:failed` untuk melihat kegagalan; periksa penyebab di log privat, perbaiki, lalu `php artisan queue:retry <id>` hanya untuk job yang dipahami. Jangan menghapus histori untuk menyembunyikan masalah.
- Jika Reverb mati, form HTTP tetap berfungsi dan UI polling 20 detik. Setelah pulih, UI mengambil status database terbaru. FINAL tidak perlu dihitung ulang akibat transport gagal.
- Untuk PDF hilang/job piagam gagal, gunakan tombol pembuatan/pemulihan pada hasil FINAL Admin. UUID/file yang tersedia tidak ditimpa; metadata snapshot dipertahankan. Pada FINAL lama Tahap 7 periksa tanggal event sebelum backfill pertama, karena snapshot lama tidak memuat tanggal/penyelenggara.
- Jika tanda tangan/stempel dibutuhkan, gunakan aset resmi dan versi template baru melalui perubahan yang direview. Jangan menyunting dokumen historis diam-diam.

## Backup dan pemulihan

Backup mencakup MySQL, storage private/public, serta APP_KEY dan konfigurasi penting melalui penyimpanan rahasia terpisah. Database saja tidak dapat memulihkan bukti pembayaran/PDF. Gunakan snapshot yang konsisten; hentikan penulisan web dan worker ketika metode backup membutuhkan quiescence. Tentukan retensi, enkripsi, akses minimum dan salinan di luar server. Jangan commit hasil backup.

Uji restore pada environment terisolasi dengan email/queue/broadcast eksternal dimatikan. Cocokkan pembayaran, score/violation, FINAL, metadata piagam dan keberadaan file. Jangan menjalankan migrate:rollback atau mengubah rank FINAL sebagai cara memperbaiki deployment. Untuk rollback aplikasi, pastikan release lama kompatibel dengan schema terbaru; perubahan schema/data yang tidak kompatibel membutuhkan rencana restore/migrasi tersendiri. Restore rehearsal belum dijalankan karena tidak ada target/backup yang diberikan.
