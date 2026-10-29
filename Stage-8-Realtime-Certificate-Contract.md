# Tahap 8 — Realtime dan Piagam Pemenang

Tanggal: 29 Oktober 2026. Status: selesai diimplementasikan dan siap direview. Tahap 9 belum dimulai.

## Hasil implementasi

- Laravel Reverb + Echo/Pusher pada Blade: progres publik, monitor Owner/Admin, status Juri, pembaruan ranking internal, dan kesiapan piagam peserta.
- Status Connected / Reconnecting / Offline fallback. Saat realtime terputus, pembacaan HTTP setiap 20 detik; nilai dan operasi tetap disimpan melalui request biasa. Formulir tidak dihapus atau otomatis dimuat ulang.
- Kanal privat diperiksa terhadap identitas, kepemilikan event, event_admins, atau judge_assignments aktif. Snapshot HTTP memeriksa scope lagi; payload publik tidak memuat nilai Juri, ranking provisional, kontak, atau catatan internal.
- Broadcast berupa ID setelah commit, melalui database queue. Event hanya memicu pembacaan state database terbaru. Kegagalan Reverb tidak membatalkan nilai/DQ/finalisasi.
- Piagam PDF otomatis diantrekan setelah FINAL. Penerima hanya posisi hadiah dalam snapshot FINAL; peringkat resmi hasil dadu dipakai ketika draw sudah diselesaikan. Tidak hardcode top 3.
- Menu Piagam peserta: daftar milik sendiri, status menunggu, preview/cetak dan unduh. Owner/Admin event dapat mengakses PDF sesuai scope; Juri atau peserta lain tidak dapat mengunduh.
- Admin dapat mengantrekan pembuatan/pemulihan dari halaman hasil FINAL, termasuk FINAL lama. Retry tidak menggandakan record/audit atau menimpa PDF yang tersedia. File hilang dipulihkan dari metadata piagam tersimpan.
- Template A4 landscape forest/gold, nomor piagam dan identitas lengkap sesuai FINAL; tanpa tanda tangan/stempel rekaan.

## Database dan kompatibilitas

Migrasi baru `2026_10_29_190259_create_certificates_table.php` sudah diterapkan di database lokal (batch 9), setelah pemeriksaan SQL `--pretend`. Tidak ada reset, wipe, atau seed database development.

Tabel menyimpan FK event/class/registration/FINAL result, rank INT, UUID private path, template version, metadata snapshot dan generated_at. Unique ranking_result_id, FK RESTRICT dan indeks event/class melindungi integritas/riwayat. Model piagam tidak dapat di-update/delete lewat alur aplikasi. Penerbitan mencatat `CERTIFICATE_GENERATED`; file tidak tersedia melalui `/storage` publik. Policy memeriksa kecocokan seluruh rantai result–registration–class–event–rank dan eligibility FINAL.

FINAL baru menyimpan tanggal event dan penyelenggara dalam snapshot. FINAL Tahap 7 lama tidak diubah: bila metadata belum tersedia, tanggal event saat penerbitan pertama dan nama aplikasi dibekukan di metadata piagam. **Periksa tanggal event historis sebelum backfill piagam lama.** Nilai/rank/hadiah/identitas peserta tetap memakai FINAL. Penambahan metadata input menyebabkan provisional/draw dari versi sumber lama perlu dihitung dan ditinjau ulang sebelum finalisasi; keputusan lama tetap tersimpan sebagai riwayat.

File storage dan DB tidak mempunyai transaksi bersama. Cleanup file baru dilakukan jika pembuatan record/audit gagal dalam action; hard crash tetap dapat meninggalkan file yatim. Job production memanggil action setelah transaksi FINAL commit. Retry aman dan tidak mengubah hasil FINAL.

## Peta file perubahan

| Area | File utama |
| --- | --- |
| Runtime realtime | `config/broadcasting.php`, `config/reverb.php`, `.env.example`, `bootstrap/app.php`, `routes/channels.php`, dependency Composer/npm beserta lockfile |
| Event/listener | `ClassStateChanged`, `CertificateReady`, `CertificateGenerated`, `RankingUpdated`, `JudgingStateChanged`, `BroadcastClassChanges`, `BroadcastCertificateReady`, `QueueWinnerCertificates` |
| Status dari DB | `LiveClassSnapshot`, `LiveClassController`, routes public/Owner/Admin/Juri dan fragment `RankingController` |
| Frontend | `resources/js/live-monitor.js`, `realtime.js`, `components/live-class.blade.php`, view event/Owner/Admin/Juri, layout dashboard dan halaman Piagam |
| Piagam | `app/Domain/Certificate`, `GenerateWinnerCertificates`, `Certificate`, `CertificatePolicy`, `CertificatePdfController`, participant `CertificateController`, `WinnerCertificateController`, factory/migration |
| Snapshot | `RankingInputBuilder`, relasi `RankingResult::certificate`, `ActivityAction::CertificateGenerated`, morph map provider |
| PDF | `resources/views/pdf/certificate.blade.php` |
| Tes | `tests/Feature/Certificate`, `tests/Feature/Realtime`, `tests/JavaScript/live-monitor.test.js` |
| Dokumentasi | `Realtime.md`, PRD, Design, Architecture, Schema, TOPSIS, AGENTS dan kontrak ini |

Dependensi Reverb membutuhkan Guzzle 7/PSR-7 2 pada lockfile saat ini; framework Laravel tetap 13.32.0. Pembaruan patch dependency kompatibel juga menutup advisory yang ditemukan. Tidak menambah Redis sebagai kebutuhan.

## Verifikasi

- Baseline sebelum perubahan: 209 tes PHP / 1.077 assertion lulus.
- Setelah implementasi: **232 tes PHP / 1.218 assertion lulus**, MySQL test terpisah. Termasuk akses negatif lintas event/identitas, private channel, whitelist publik, rollback/after-commit, HTTP saat Reverb mati, retry/idempotency, missing file, kegagalan renderer/audit, snapshot dan pemenang dadu.
- **6 tes JavaScript lulus** menggunakan Node bawaan; meliputi fallback, connected/recovery, tab tersembunyi, akses dicabut dan request tidak bertumpuk.
- Uji Reverb nyata lokal: WebSocket upgrade, subscribe, signed PHP broadcast dan penerimaan pesan berhasil. Server QA port 18081 dihentikan; server web pengguna tetap berjalan.
- PDF normal dan nama panjang dirender dengan renderer aplikasi, diperiksa satu halaman dan dilihat secara visual; tidak terpotong/bertumpuk.
- Pint pada file PHP tahap ini, Blade view cache, build Vite dan pemeriksaan whitespace berhasil. Composer audit dan npm audit: tidak ada advisory yang terdeteksi saat verifikasi.

Belum diklaim: uji beban serentak besar, reverse proxy/TLS produksi, uji browser lintas perangkat pada seluruh viewport. Peringatan optional fontaine saat build Vite tidak menggagalkan build.

## Menjalankan dan review lokal

Web server development yang ada tetap berjalan. Untuk pembaruan langsung dan pembuatan PDF, jalankan di terminal terpisah:

```powershell
php artisan queue:work database --tries=3 --timeout=60
php artisan reverb:start
```

Asset build sudah diperbarui; gunakan `npm run dev` bila mengembangkan frontend. Kredensial Reverb lokal telah dihasilkan installer dalam `.env` yang diabaikan Git. Petunjuk host/origin, instalasi baru, retry failed job dan lifecycle ada di `Realtime.md`.

Urutan review:

1. Buka event berlangsung sebagai publik: progres tampil, nilai/ranking provisional tidak tersedia. Bandingkan dengan monitor Admin/Owner dan kelas Juri.
2. Dengan worker/Reverb aktif, submit nilai atau catat DQ yang sah; periksa progres/tabel berubah pada sesi lain. Matikan Reverb untuk mencoba Offline fallback tanpa kehilangan input formulir Juri.
3. Finalisasi kelas yang memenuhi syarat. Setelah worker memproses, pemenang posisi hadiah mendapat Piagam di akun peserta. Coba preview, cetak dan unduh; akun lain harus ditolak.
4. Pada hasil FINAL lama yang tanggal eventnya sudah diperiksa, gunakan tombol pembuatan/pemulihan piagam Admin. Jalankan ulang; dokumen yang sudah ada tetap sama.
5. Review tampilan piagam, teks dan alur mobile. Format tanda tangan/stempel masih menunggu aset resmi; tidak dibuat otomatis.

## Tahap berikutnya

**Tahap 9 — Hardening dan QA akhir:** review alur menyeluruh, perbaikan dari review Tahap 8, pemeriksaan responsive 360–1440 px dan aksesibilitas, pengujian konkurensi/performa yang relevan, serta panduan operasional/deployment. Tidak melakukan deployment tanpa instruksi tersendiri. Berhenti setelah Tahap 8 agar pengguna dapat review/revisi terlebih dahulu.
