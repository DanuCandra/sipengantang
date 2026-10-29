# Realtime — Kontrak Tahap 8

Implementasi 29 Oktober 2026. Laravel Blade tetap renderer utama. Semua perubahan data melalui HTTP berotorisasi; Reverb hanya mengirim sinyal agar UI membaca ulang database.

## Kanal dan privasi

| Kanal Echo | Akses | Event / payload |
| --- | --- | --- |
| `class.{id}.public` | Publik, hanya kelas pada event terbit yang bukan DRAFT/CANCELLED | `.class.updated`, `class_id` |
| `event.{id}.admin` (private) | Owner event / Admin yang ditugaskan | `.class.updated`, `class_id` |
| `class.{id}.judge.{judgeId}` (private) | Juri aktif dengan penugasan kelas aktif dan identitas sendiri | `.class.updated`, `class_id` |
| `user.{id}` (private) | Akun aktif milik sendiri | `.certificate.ready`, `certificate_id` |

Otorisasi kanal ada di `routes/channels.php`, menggunakan session, middleware active, CSRF, dan throttle. Pencabutan penugasan dipastikan lagi pada setiap pembacaan HTTP; koneksi lama hanya mungkin menerima ID tanpa data privat. Client event dinonaktifkan; origin lokal dibatasi localhost/127.0.0.1. Domain produksi harus dikonfigurasi eksplisit.

## Pembacaan status

- Publik: `GET /events/{slug}/classes/{class}/live` berisi progres agregat, status kelas, jumlah DQ, dan URL hasil FINAL. Tidak berisi nilai, rank provisional, identitas Juri, catatan, kontak, atau matriks.
- Admin/Owner: endpoint nested di area masing-masing, dengan pemeriksaan rantai event–kelas. Tambahan progres per Juri dan ID/jenis run untuk pembaruan tabel internal.
- Juri: `GET /judge/classes/{class}/live?registrations[]=...` hanya status peserta kelas tersebut dan status evaluasi Juri sendiri; maksimum 15 ID. Tidak mengirim nilai Juri lain.
- Peserta: `GET /participant/certificates/live?results[]=...` hanya hasil miliknya yang memenuhi syarat piagam, maksimum 12 ID; URL terotorisasi tanpa storage path.
- Semua respons status memakai `private, no-store`. Fragment ranking internal dirender di Blade dan diambil lewat otorisasi yang sama seperti halaman asal.

## Perilaku klien

`resources/js/live-monitor.js` mengatur lifecycle; `realtime.js` menghubungkan Echo dan DOM. Awal halaman membaca status lewat HTTP. Setelah subscribed tampil Connected dan polling dihentikan. Putus koneksi menampilkan Reconnecting, lalu Offline fallback; polling setiap 20 detik. Ketika tersambung kembali atau tab kembali terlihat, baca ulang sumber HTTP.

Request tidak bertumpuk; tab tersembunyi tidak mengambil snapshot. Timeout/transient error memulai fallback. Respons 401/403/404 menghentikan pemantauan dan meminta muat ulang. Formulir tidak di-reload atau dihapus. DQ, submitted milik sendiri, dan FINAL menonaktifkan tombol relevan; backend tetap otoritatif. Perubahan tabel ranking tidak menimpa formulir keputusan draw/finalisasi: petugas mendapat pemberitahuan untuk memuat ulang sebelum mengambil keputusan.

## Event, queue, dan kegagalan

Event domain dikirim setelah commit. `ClassStateChanged`/`CertificateReady` memakai database queue secara eksplisit, termasuk ketika default queue sync. Kegagalan broadcast tidak membatalkan nilai, DQ, atau hasil FINAL yang sudah tersimpan. Retry broadcast terbatas; fallback HTTP tetap berfungsi tanpa worker/Reverb. Event yang tertunda hanya memicu pembacaan status terkini, bukan menerapkan state lama.

Minimum runtime lokal, di terminal terpisah:

```powershell
php artisan queue:work database --tries=3 --timeout=60
php artisan reverb:start
npm run dev
```

Laravel web server juga harus berjalan. Worker default menangani provisional ranking, piagam, dan broadcast; jangan menjalankan worker kedua dengan konfigurasi queue yang berbeda tanpa memahami beban yang diproses. Database queue `retry_after=90` lebih besar daripada timeout worker 60 detik. Redis tidak dibutuhkan.

Untuk instalasi baru: `composer install`, `npm install`, siapkan `.env`, jalankan `php artisan reverb:install`, lalu migrasi. Installer menghasilkan kredensial lokal; jangan commit `.env`. `BROADCAST_CONNECTION=reverb`, host/port/scheme server dan `VITE_REVERB_*` harus selaras. Rebuild `npm run build` setelah perubahan variabel Vite. `.env.example` berisi opsi host/origin, tanpa secret. Instalasi lokal tahap ini sudah menghasilkan kredensial dan migrasi piagam telah diterapkan.

Pemulihan: periksa `php artisan queue:failed`, perbaiki penyebab lalu retry job tertentu. Admin dapat memakai tombol pembuatan/pemulihan piagam pada hasil FINAL. Job idempoten; jangan mengubah snapshot FINAL untuk memperbaiki kegagalan transport/PDF. Proses QA Reverb port 18081 telah dihentikan; layanan development milik pengguna tidak dihentikan.

## Verifikasi dan batas

Tes kanal positif/negatif, whitelist publik, isolasi Juri, commit/rollback, snapshot lintas event, fragment escaped, dan HTTP saat Reverb mati ada di `tests/Feature/Realtime`. Enam tes Node meliputi polling, reconnect/recovery, tab tersembunyi, penghentian akses, dan request bertumpuk. Uji wire lokal berhasil melakukan WebSocket upgrade, subscribe, signed PHP broadcast, dan penerimaan pesan pada Reverb nyata.

Belum dilakukan uji beban banyak koneksi, pengujian reverse proxy/TLS produksi, serta pemeriksaan browser di seluruh viewport perangkat. Itu masuk review dan hardening Tahap 9.
