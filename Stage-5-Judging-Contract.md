# Tahap 5 - Penugasan Juri & Penilaian

Status: selesai dan siap direview.

## Ruang lingkup

- Admin event dapat memilih akun aktif berdasarkan email untuk ditugaskan sebagai Juri kelas. Peran Juri ditambahkan bila belum ada, tanpa menghapus peran sebelumnya atau membuat akun duplikat.
- Admin dapat membuat akun Juri baru sekaligus menugaskannya ke kelas dalam satu transaksi.
- Peran global Juri tidak memberi akses ke kelas lain. Semua pembacaan dan penulisan penilaian memerlukan penugasan kelas.
- Admin membuka penjurian lewat aksi **Mulai penjurian**. Kelas `UPCOMING` menjadi `ONGOING`; event yang masih `UPCOMING` juga menjadi `ONGOING`. Event harus sudah dipublikasikan dan snapshot lima kriteria harus lengkap.
- Perubahan event menjadi berlangsung mengikuti alur pendaftaran Tahap 3: pendaftaran baru tidak lagi dibuka pada event tersebut. Pembayaran yang sudah terdaftar tetap mengikuti alur Tahap 4.
- Juri melihat daftar kelas penugasan, peserta terverifikasi, pencarian nomor gantangan/pendaftaran atau nama burung, dan progres nilai miliknya.
- Peserta DQ tetap terlihat sebagai informasi; tidak dapat dinilai lagi. Riwayat nilai yang sudah ada tetap dapat dibaca.
- Form nilai menggunakan lima snapshot kriteria kelas, dengan kontrol angka 1-10 yang dapat digunakan melalui sentuhan atau keyboard.
- Draft boleh parsial, dapat dilanjutkan, dan belum dihitung sebagai evaluasi selesai.
- Submit memerlukan satu nilai bulat valid untuk seluruh kriteria kelas. Kriteria dari kelas lain ditolak.
- Setelah `SUBMITTED`, skor dan waktu submit tidak dapat ditimpa oleh submit berulang ataupun draft yang terlambat.
- Admin melihat progres per Juri dan jumlah evaluasi terkirim melalui pembacaan database. Tombol muat ulang tetap berfungsi tanpa JavaScript atau Reverb.

## Penyimpanan dan keamanan

Tiga tabel mengikuti `Schema.md`:

```text
judge_assignments: unique(competition_class_id, judge_id)
judge_evaluations: unique(registration_id, judge_id)
judge_scores: unique(judge_evaluation_id, criterion_id)
```

Foreign key melindungi sejarah penilaian dari hard-delete. Skor memakai integer; identitas Juri, status evaluasi, dan waktu submit selalu ditetapkan server.

Penyimpanan nilai mengunci kelas, pendaftaran, lalu evaluasi di dalam transaksi. Status pendaftaran, status kelas/event, penugasan, dan status evaluasi diperiksa ulang sebelum perubahan. Ini melindungi submit ganda dan mencegah draft menimpa nilai terkirim. Audit `SCORE_DRAFT_SAVED` atau `SCORE_SUBMITTED` ikut dalam transaksi. `JudgeScoreSubmitted` baru dikirim setelah commit dan hanya membawa ID, tanpa nilai pribadi.

Query halaman Juri hanya memuat evaluasi Juri yang sedang masuk. Nilai Juri lain, email/telepon peserta, serta data pembayaran tidak dikirim ke halaman tersebut. Admin yang tidak ditugaskan dan resource lintas event/kelas ditolak server.

## Batas tahap dan keputusan terbuka

- Jumlah minimum Juri dan penanganan Juri absen belum ditentukan. Tampilan progres memakai jumlah peserta terverifikasi dikali seluruh penugasan saat ini, sebagai indikator operasional dan bukan syarat finalisasi.
- Tidak ada edit-after-submit, penghapusan penugasan bersejarah, atau penilaian ulang terselubung.
- Tidak ada penyelesaian/finalisasi otomatis ketika progres mencapai 100%.
- Peringatan/DQ merupakan Tahap 6; perhitungan rata-rata/TOPSIS dan finalisasi merupakan Tahap 7; realtime merupakan Tahap 8.
- Domain event penilaian telah tersedia untuk integrasi tahap lanjutan. Belum ada kalkulasi ranking atau broadcast pada tahap ini.

## Skenario review

1. Login Admin yang ditugaskan, buka **Event → Kelas → Kelola Juri & penjurian**.
2. Tugaskan akun aktif yang ada, atau buat akun Juri baru. Ulangi penugasan yang sama dan pastikan tidak terjadi duplikasi.
3. Pastikan event telah dipublikasikan, lalu klik **Mulai penjurian**.
4. Login sebagai Juri. Buka **Penugasan**, pilih kelas, lalu buka lembar nilai peserta terverifikasi.
5. Isi sebagian nilai dan pilih **Simpan draft**. Buka kembali untuk memastikan nilai tersimpan.
6. Coba submit nilai belum lengkap, lalu lengkapi seluruh kriteria dan submit kembali.
7. Pastikan nilai terkirim hanya dapat dibaca. Buka akun Juri kedua untuk memastikan nilai Juri pertama tidak terlihat.
8. Login Admin dan muat ulang progres untuk melihat evaluasi terkirim. Draft tidak dihitung selesai.

## Hasil verifikasi

- Seluruh suite: 136 test dan 598 assertion lulus.
- Suite terarah penjurian dan dashboard: 51 test dan 246 assertion lulus.
- Laravel Pint, kompilasi Blade, pemeriksaan sintaks JavaScript, dan build Vite berhasil.
- Audit Composer tidak menemukan advisory kerentanan.
- Ketiga migration penjurian sudah diterapkan ke database lokal tanpa mereset data lama.
- Form memakai layout responsif lima/sepuluh kolom. Pemeriksaan visual browser pada ukuran layar yang ditentukan belum dilakukan; review langsung tampilan ponsel tetap diperlukan.
- Build masih menampilkan peringatan dependency `fontaine` opsional untuk optimasi font fallback yang telah ada pada tahap sebelumnya; build tetap berhasil.

## Berkas utama

Implementasi berada pada `app/Domain/Judging/`, tiga model `JudgeAssignment`, `JudgeEvaluation`, dan `JudgeScore`, `JudgeEvaluationPolicy`, controller/request Admin dan Juri, `routes/admin.php`, `routes/judge.php`, serta `resources/views/admin/judges/` dan `resources/views/judge/`. Pengujian berada pada `tests/Feature/Judging/` dan fixture pada `tests/Concerns/CreatesJudgingFixtures.php`. Kontrak terkait diperbarui pada `Schema.md`, `Architecture.md`, dan `Design.md`.

## Tahap berikutnya

**Tahap 6 - Peringatan, Pelanggaran & Diskualifikasi**: pencatatan alasan/catatan, peringatan pertama, eskalasi pelanggaran berulang, DQ langsung, audit, dan pelestarian sejarah nilai.
