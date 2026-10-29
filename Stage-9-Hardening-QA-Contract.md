# Tahap 9 — Hardening dan QA Akhir

Tanggal: 29 Oktober 2026. Implementasi dan verifikasi otomatis selesai; siap untuk review pengguna. Verifikasi visual browser lintas viewport masih belum terverifikasi karena browser automation host tidak tersedia. Tidak ada deployment pada tahap ini.

Pengguna mengizinkan melanjutkan tahap berikutnya dan akan review setelah pekerjaan tersisa selesai. Tidak dilakukan penghentian tambahan untuk persetujuan rutin. Aturan bisnis belum diputuskan tetap memakai safe defaults dan tidak ditebak.

## Perbaikan

1. **Navigasi dashboard mobile:** sidebar tertutup tidak dapat menerima fokus, menu terbuka menjadi dialog berlabel dengan fokus di dalamnya, Escape/Tutup/backdrop mengembalikan fokus, background inert, scroll dikunci sementara, dan perubahan breakpoint desktop membersihkan modal. Ditambahkan skip link, scroll sidebar, fallback tanpa JavaScript, serta reduced motion.
2. **Formulir:** error dihubungkan ke input melalui aria-invalid/aria-describedby dan ID error; ID khusus didukung agar dua formulir email di halaman Owner tidak memakai ID sama. Helper text dipertahankan saat error. Label/placeholder yang diperbaiki memakai kontras lebih jelas.
3. **Copy operasional:** dashboard Owner/Admin tidak lagi mengumumkan “Tahap 2” atau menjanjikan menu “Segera”. Arah tindakan memakai daftar event aktif. Copy login memakai alur lomba yang sudah tersedia. Nama panjang pada kartu/lembar nilai Juri membungkus.
4. **Isolasi tes:** TestDatabaseGuard menolak database selain sipengantang_test, environment selain testing, driver selain mysql dan DB_URL yang dapat mengalihkan koneksi, sebelum refresh database. Berlaku pula untuk proses anak konkurensi.
5. **QA alur lengkap:** satu alur HTTP nyata dari pembuatan event/penugasan/kelas/hadiah, publikasi, burung/daftar, upload/verifikasi transfer, penilaian tiga Juri, finalisasi, pengunduhan piagam, hingga event selesai. Memeriksa hasil publik, akses piagam peserta lain, dan finalisasi berulang ditolak.
6. **Konkurensi:** empat uji contention nyata dua proses PHP/MySQL untuk kuota terakhir, keputusan pembayaran yang bertentangan, warning/DQ, dan finalisasi. Kedua koneksi harus terlihat LOCK WAIT sebelum dilepas; ini tidak bergantung pada urutan pemenang race.
7. **Query/performa:** progres publik tetap memakai query agregat ketika peserta bertambah 2→52; daftar Juri tetap terpaginasikan 15 dan tidak menambah query per peserta ketika data bertambah 2→32.
8. **Dokumentasi:** README menggantikan boilerplate Laravel; Testing.md dan Deployment.md menjelaskan instalasi, layanan, isolasi tes, rilis, backup, recovery, serta batas verifikasi. npm test dan composer test:concurrency tersedia.

## Hasil verifikasi

| Pemeriksaan | Hasil |
| --- | --- |
| Unit/Feature PHP, MySQL terpisah | **245 lulus / 1.312 assertion** |
| Suite konkurensi opt-in | **4 lulus / 38 assertion** |
| JavaScript | **15 lulus** |
| Laravel Pint pada PHP tahap ini | Lulus |
| Vite build | Lulus |
| Blade view cache | Lulus |
| Route cache, lalu dikembalikan tidak ter-cache | Lulus |
| Composer validate | Lulus |
| Composer audit / npm audit | Tidak ada advisory terdeteksi |
| git diff --check | Lulus |
| Smoke HTTP server lokal `/`, `/events`, `/login` | 200, viewport dan asset build tersedia |
| Migrasi database aplikasi | Tidak ada perubahan/pending baru; tabel piagam tetap batch 9 |

Suite PHP utama dan konkurensi dijalankan **bergantian**. Selama pengembangan harness, tes gagal karena buffering stdout proses anak Windows; diperbaiki dengan fwrite/fflush dan lock barrier database. Uji alur awal juga menemukan payload konfirmasi event SELESAI yang belum dikirim oleh tes; diperbaiki agar memakai kontrak endpoint yang sebenarnya. Tidak ada test dihapus untuk meluluskan suite. Assertion aksesibilitas diuji melalui Blade dengan error bag terkontrol; validasi/login HTTP tetap tercakup suite auth.

Peringatan optional fontaine saat build tetap nonblocking; tidak menambah dependency hanya untuk menghilangkan pesan tersebut. Audit dependency adalah hasil pada saat pengujian, bukan jaminan tidak ada advisory baru di masa depan.

## File tahap ini

- UI: resources/js/dashboard-navigation.js, app.js, resources/css/app.css; layout dashboard/guest, komponen form field, dashboard/index, event/show, owner/event/show, dan kartu/lembar nilai Juri.
- Tests: tests/TestCase.php, TestDatabaseGuard.php, Unit/TestDatabaseGuardTest.php, Concurrency/CompetitionConcurrencyTest.php, Support/concurrent-operation.php, Feature/Workflow/CompetitionLifecycleTest.php, Feature/Performance/OperationalQueryTest.php, Feature/Accessibility/FormAccessibilityTest.php, JavaScript/dashboard-navigation.test.js.
- Scripts/docs: composer.json, package.json, README.md, Testing.md, Deployment.md, AGENTS.md, Architecture.md, Design.md, dan kontrak ini.

Perubahan Stage 2–8 yang sudah ada dalam working tree tetap dipertahankan. Tidak ada migration/schema/domain ranking baru, reset/seed database aplikasi, commit, pengiriman pesan, atau deployment. Server web, worker dan Reverb development milik pengguna yang sudah berjalan tidak dihentikan.

## Batas yang masih perlu diverifikasi

- **Visual browser:** inventory browser dan pengambilan browser URL lokal gagal dengan “browser access host is unavailable”; native pipe juga tidak tersedia. Belum ada screenshot atau pengujian visual responsive pada 360/390/768/1024/1280/1440 px. Tes Node hanya memeriksa state/fokus pada breakpoint tersebut, bukan layout aktual. Checklist pemeriksaan manual ada di Testing.md. Ini adalah keterbatasan verifikasi lingkungan, bukan pekerjaan yang dianggap lulus.
- **Hosting:** reverse proxy, TLS, kapasitas banyak koneksi/peserta, backup/restore rehearsal, mailer nyata dan credential production membutuhkan lingkungan target. Tidak ada klaim siap produksi tanpa pemeriksaan tersebut.
- **Aset/keputusan:** tanda tangan/stempel resmi piagam belum diberikan. Partial-submission judge replacement, koreksi submitted, pembatalan DQ dan pertanyaan terbuka lain tidak diimplementasikan diam-diam. Aturan tiga Juri, pengganti tanpa submitted, dan dadu nyata tetap mengikuti keputusan Tahap 7.
- **Historis:** untuk piagam FINAL lama, tanggal event harus diperiksa sebelum backfill pertama sesuai kontrak Tahap 8. Riwayat FINAL tidak dihitung ulang atau ditulis ulang.

## Setelah ini

Implementasi MVP Tahap 0–9 tersedia untuk review. Tidak ada tahap fitur tambahan yang dijalankan otomatis. Langkah berikutnya adalah review pengguna dan menyelesaikan validasi visual saat browser tersedia; deployment dilakukan hanya bila diminta dengan target yang jelas. Panduan operasional sudah tersedia untuk menyiapkan langkah tersebut.
