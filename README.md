# SIPENGANTANG / GantangScore

Aplikasi Laravel + Blade untuk penyelenggaraan lomba gantangan burung: event, kelas, pendaftaran, pembayaran manual, penjurian, peringatan/DQ, TOPSIS per kelas, hasil FINAL dan piagam PDF.

Database adalah sumber kebenaran. Reverb memperbarui tampilan; semua penulisan melalui HTTP berotorisasi. Redis tidak dibutuhkan untuk MVP.

## Persiapan lokal

Gunakan versi PHP yang memenuhi `composer.lock`, Composer, Node/npm, dan MySQL. Jalankan perintah dari direktori repository. File `.env`, dokumen privat dan database asli tidak masuk Git.

```powershell
composer install
npm install
Copy-Item .env.example .env
php artisan key:generate
```

Salin `.env.example` hanya pada instalasi baru. Pada instalasi yang ada, pertahankan `.env` dan APP_KEY. Atur koneksi MySQL development ke database aplikasi; database tes harus terpisah bernama `sipengantang_test`.

```powershell
php artisan migrate
php artisan reverb:install
npm run build
```

Installer Reverb menghasilkan kredensial lokal dan variabel Vite. Bila mengubahnya, jalankan build lagi. Seeder demo hanya untuk development baru: `php artisan db:seed`. Jangan jalankan seeder pada data yang sudah digunakan; seeder demo dapat memperbarui akun demo yang ada.

## Menjalankan

Akun seeder lokal: Owner `owner@gmail.com`, Admin `albab@gmail.com`, Juri `juri@gmail.com`, Peserta `peserta@gmail.com`. Kata sandi seluruhnya `123` (konfigurasi `GANTANGSCORE_DEMO_PASSWORD`).

Jalankan di terminal terpisah:

```powershell
php artisan serve
php artisan queue:work database --tries=3 --timeout=60
php artisan reverb:start
npm run dev
```

`npm run dev` dapat diganti `npm run build` untuk asset tersimpan. Worker memproses ranking provisional, piagam dan broadcast. Aplikasi tetap dapat dipakai tanpa Reverb; pembaruan status beralih ke HTTP setiap 20 detik.

## Pengujian

```powershell
composer test
npm test
composer test:concurrency
npm run build
```

Jalankan tes PHP utama dan konkurensi **bergantian**, karena memakai database tes yang sama. Tes dapat mereset database tes tersebut. Pengaman menolak environment/database lain sebelum refresh. Jangan memakai database development/produksi sebagai database tes. Uji konkurensi memerlukan akses MySQL `information_schema.innodb_trx`; lihat [Testing.md](Testing.md).

## Dokumen acuan

- [AGENTS.md](AGENTS.md): aturan kerja repository.
- [PRD.md](PRD.md), [Design.md](Design.md), [Architecture.md](Architecture.md), [Schema.md](Schema.md): kontrak produk dan teknis.
- [TOPSIS.md](TOPSIS.md): input, rumus, draw dadu nyata dan finalisasi.
- [Realtime.md](Realtime.md): kanal, privasi, fallback dan recovery.
- [Testing.md](Testing.md): cakupan, perintah dan batas verifikasi.
- [Deployment.md](Deployment.md): persiapan rilis, backup, queue, Reverb dan pemulihan.
- [Tahap 9](Stage-9-Hardening-QA-Contract.md): hasil hardening, pengujian dan pekerjaan yang membutuhkan lingkungan browser/hosting.

Implementasi MVP sampai Tahap 9 tersedia untuk review. Repository belum dideploy oleh agen. Tanda tangan/stempel piagam menunggu aset resmi; pemeriksaan visual browser seluruh viewport belum dapat dilakukan karena browser automation host tidak tersedia.
