# Pengujian SIPENGANTANG / GantangScore

Kontrak verifikasi Tahap 9, 29 Oktober 2026. Tes memakai kode domain, policy, route dan Blade aplikasi yang sebenarnya. Tidak memerlukan Redis atau Reverb aktif untuk suite utama.

## Isolasi database

`phpunit.xml` menunjuk MySQL `sipengantang_test`. Buat database kosong tersebut dan beri akun tes akses hanya ke database tes bila lingkungan memungkinkan. Konfigurasi development tetap memakai database aplikasi yang berbeda. Jangan menyalin data peserta/pembayaran asli ke fixture.

`Tests\TestDatabaseGuard` dijalankan dari `TestCase::createApplication` sebelum trait refresh database, serta dari proses anak suite konkurensi. Hanya APP_ENV=testing, koneksi mysql, nama database tepat sipengantang_test dan DB_URL kosong yang diizinkan. Config cache yang mengarah ke database lain juga ditolak. Pengaman ini bukan sandbox MySQL: gunakan akun database tes yang terpisah agar batas izin berlaku pada server juga.

Suite utama memakai `LazilyRefreshDatabase`. Suite konkurensi memakai `DatabaseMigrations`, karena fixture harus committed dan terlihat oleh proses lain. Keduanya dapat membuat ulang tabel database tes. **Jangan menjalankan suite PHP utama dan konkurensi secara bersamaan.** Jangan menambahkan `--parallel` tanpa menyesuaikan database, guard, storage dan cache per proses.

## Perintah

```powershell
composer test
npm test
composer test:concurrency
npm run build
composer validate --no-check-publish
composer audit
npm audit
```

`composer test` membersihkan config cache lalu menjalankan Unit/Feature sesuai phpunit.xml. `composer test:concurrency` menjalankan suite opt-in di tests/Concurrency. Untuk satu area gunakan `php artisan test --compact --filter=NamaTest` atau path test. Formatter yang tersedia adalah `php vendor/bin/pint`; arahkan ke file yang berubah, lalu gunakan `--test` untuk pemeriksaan. Tidak ditambahkan framework tes/frontend baru.

## Cakupan utama

| Area | Perilaku yang diuji |
| --- | --- |
| Identitas | Session auth, peran ganda, status akun, redirect, reset password, pencegahan role injection |
| Event | Owner, Admin event-scoped, kelas/kriteria/hadiah, publikasi, nested resource, snapshot |
| Peserta | Profil/burung, kepemilikan, jenis burung, periode pendaftaran, kuota dan duplikasi |
| Pembayaran | File privat, upload/verify/reject/re-upload, sejarah, akses negatif, rollback dan review berulang |
| Penjurian | Penugasan, pengganti, 1–10, kriteria lengkap, draft/submitted, isolasi Juri, submitted immutable |
| Pelanggaran | Warning pertama, eskalasi, DQ langsung, konfirmasi/catatan, riwayat nilai tetap ada |
| Ranking | Fixture independen TOPSIS, presisi, edge case, DQ/draft exclusion, draw dadu nyata, hash sumber, snapshot FINAL dan penguncian |
| Realtime | Kanal privat, payload publik minimal, HTTP tanpa Reverb, after-commit/rollback, fragment escaped |
| Piagam | Posisi hadiah dari FINAL, pemenang dadu resmi, snapshot, PDF privat, retry/idempotency, file hilang, kegagalan renderer/audit |
| Alur lengkap | Event → penugasan → kelas/hadiah → publikasi → burung/daftar → transfer/verifikasi → 3 Juri → FINAL → PDF → event selesai |
| Aksesibilitas | Label/error ID, helper text, skip link, navigasi keyboard, Escape/fokus kembali, inert pada panel tersembunyi |

Node test memakai runner bawaan, tanpa jsdom. Adapter elemen ringan menguji perubahan fokus/ARIA dan state menu pada breakpoint 360/390/768/1024/1280/1440; ini **bukan** screenshot atau verifikasi layout browser. LiveMonitor diuji untuk polling, reconnect/recovery, tab tersembunyi, akses dicabut dan request yang tidak bertumpuk.

## Konkurensi nyata

`tests/Concurrency/CompetitionConcurrencyTest.php` menjalankan dua proses PHP/MySQL independen. Parent memegang lock kelas, memulai kedua proses, dan memeriksa kedua koneksi benar-benar berada pada `LOCK WAIT` di `information_schema.innodb_trx` sebelum melepas lock. Tidak ada urutan pemenang race yang diasumsikan.

Empat skenario: satu slot terakhir, keputusan pembayaran yang bertentangan, dua peringatan yang berujung DQ, dan dua finalisasi. Assertions memeriksa jumlah record/audit, konsistensi status, preservasi nilai, dan hanya satu FINAL. Diperlukan izin membaca informasi transaksi MySQL; jangan menaikkan privilege akun runtime produksi demi suite tes ini. Suite tidak memproses queue development, tidak menulis upload asli, serta mematikan proses anak miliknya saat teardown. Ini uji contention terkontrol, bukan uji beban banyak pengguna.

Revisi nomor gantangan peserta menambah dua skenario: dua pendaftaran memperebutkan nomor sama ketika kuota masih tersedia, dan dua pendaftaran lama memperebutkan nomor kosong yang sama. Total suite contention sekarang enam skenario. `GantanganNumberTest` memeriksa reservasi langsung tanpa approval Admin, normalisasi, batas scope kelas, pesan benturan, format nomor, pengisian sekali, ownership/status, audit serta perlindungan unique database dan preflight migrasi.

## Performa

`OperationalQueryTest` membandingkan jumlah query progres publik pada 2 vs 52 peserta, serta halaman Juri pada 2 vs 32 peserta. Query tetap terbatas dan halaman Juri berisi maksimum 15 peserta, dengan eager loading. Ini melindungi dari N+1; tidak membuktikan latency produksi atau kapasitas koneksi WebSocket. Benchmark kapasitas memerlukan volume realistis dan mesin hosting target.

## Verifikasi manual yang masih diperlukan

Browser automation host tidak tersedia pada sesi Tahap 9: inventory dan koneksi browser URL lokal sama-sama gagal. Oleh sebab itu tidak ada klaim screenshot/visual pass untuk seluruh viewport. Setelah browser dapat diakses, tinjau landing, login, dashboard peran, daftar/unggah pembayaran, kontrol nilai 1–10, dialog DQ, draw/finalisasi, hasil publik dan Piagam pada 360/390/768/1024/1280/1440 px. Periksa overflow, fokus keyboard, zoom 200%, nama panjang, error, empty state dan koneksi terputus. Review ponsel nyata juga dibutuhkan sebelum pemakaian lomba.

PDF normal dan nama panjang telah diverifikasi visual pada Tahap 8. Uji WebSocket nyata juga lulus pada Tahap 8. TLS/reverse proxy dan backup restore di hosting belum diuji karena belum ada target deployment. Angka hasil terbaru dicatat di kontrak Tahap 9.
