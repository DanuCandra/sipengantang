# Tahap 6 — Peringatan, Pelanggaran & Diskualifikasi

Status: selesai diimplementasikan, siap direview. Tidak melanjutkan Tahap 7 sebelum persetujuan/revisi pengguna.

## Fitur yang tersedia

- Juri yang ditugaskan dapat mencatat pelanggaran pada peserta `VERIFIED`, hanya ketika event dipublikasikan dan event/kelas sedang `ONGOING`.
- Peringatan pertama tetap `WARNING`. Pelanggaran peringatan berikutnya pada **pendaftaran yang sama** menjadi `DISQUALIFICATION`, meskipun alasan atau Juri berbeda.
- DQ langsung mendukung terlambat menggantangkan, manipulasi nomor, serta pelanggaran berat lainnya dengan penjelasan.
- Semua DQ, termasuk eskalasi, memerlukan catatan kejadian dan checkbox konfirmasi eksplisit. Alasan `OTHER` selalu memerlukan penjelasan. Peringatan pertama boleh tanpa catatan.
- DQ mengubah status dan mengisi `disqualified_at`, tetapi tidak mengubah/menghapus nilai draft, submitted, pelanggaran sebelumnya, maupun pembayaran.
- Peserta DQ tidak bisa dinilai atau diberi tindakan baru. Kelas yang sudah ditutup/finalized tidak bisa diubah melalui endpoint pelanggaran.
- Nilai submitted tetap read-only; Juri masih boleh mencatat pelanggaran selama penjurian terbuka.
- Riwayat internal menggunakan pagination dan dapat dilihat oleh Juri yang ditugaskan, Admin event terkait, serta Owner event miliknya.
- Peserta hanya melihat status peringatan/DQ miliknya. Alasan/catatan internal dan identitas pencatat tidak ditampilkan kepada peserta atau publik.

## Konsistensi dan keamanan

`RecordViolationAction` menyimpan pelanggaran, status DQ, waktu, dan activity log dalam satu transaksi. Lock berurutan: kelas, lalu pendaftaran, sama dengan penyimpanan nilai. Server memeriksa ulang penugasan dan status sebelum menghitung peringatan. Identitas Juri, pendaftaran, tindakan aktual, label alasan baku, dan waktu ditetapkan server, bukan dipercaya dari formulir.

Form peringatan lama yang dibuka sebelum Juri lain memberi peringatan tidak boleh mendiskualifikasi secara tidak sengaja. Jika hasil terbaru membutuhkan DQ tetapi catatan/konfirmasi belum ada, request ditolak tanpa menambah peringatan atau mengubah status. Pengiriman ulang DQ setelah sukses juga ditolak tanpa mengubah waktu atau menambah riwayat.

`WarningCreated` dan `ParticipantDisqualified` memakai `ShouldDispatchAfterCommit` dan hanya membawa ID. Rollback tidak mengirim event. Seluruh operasi HTTP tetap bekerja tanpa Reverb. Tidak ada endpoint pembatalan DQ, edit pelanggaran, penghapusan riwayat, atau manipulasi peringkat.

## Database

Migration baru: `2026_10_21_215252_create_violations_table.php`.

Tabel mengikuti `Schema.md`: relasi pendaftaran/Juri dengan `ON DELETE RESTRICT`, enum PHP untuk action/reason code, snapshot label/penjelasan alasan, catatan, timestamp, serta indeks pendaftaran/tindakan dan alasan. Indeks Juri didukung FK MySQL. Tidak menambahkan kolom event redundan. Migration telah diterapkan ke database lokal tanpa reset atau seeding ulang data.

## UI dan review

1. Login Juri yang ditugaskan. Buka **Penugasan → Kelas → Lembar nilai peserta** ketika penjurian berlangsung.
2. Pilih **Beri peringatan**, tentukan alasan, lalu simpan. Status peserta tetap terverifikasi; badge dan riwayat peringatan muncul.
3. Buka peserta yang sama lewat Juri kedua. Pastikan riwayat terlihat, tetapi nilai Juri pertama tetap tidak terlihat.
4. Pilih **Pelanggaran berulang → DQ**. Pastikan konsekuensi jelas, catatan dan konfirmasi wajib, lalu status menjadi Diskualifikasi.
5. Pada peserta lain, buka **Tindakan berisiko: diskualifikasi langsung**. Uji alasan baku dan Lainnya. Membatalkan dialog tidak menyimpan apa pun.
6. Coba mengirim form peringatan lama setelah Juri lain memberi peringatan. Form tanpa catatan/konfirmasi DQ harus ditolak.
7. Periksa nilai peserta DQ: riwayat tetap ada dan hanya dapat dibaca. Peserta DQ tidak lagi dihitung dalam progres peserta eligible.
8. Login Admin: **Event → Riwayat peringatan & DQ**, atau **Peserta terdaftar → Detail peserta**. Hanya catatan event terkait yang tersedia.
9. Login Owner: buka event miliknya untuk memeriksa riwayat internal event.
10. Login peserta: periksa status peringatan/DQ tanpa catatan, alasan internal, atau identitas Juri. Publik juga tidak menerima data tersebut.

Dialog native menggunakan fokus/escape bawaan browser dan ukuran mengikuti viewport; formulir inline tetap tersedia jika JavaScript tidak aktif. Tombol danger terpisah dari tombol nilai. Tampilan memakai token dan komponen tahap sebelumnya, area sentuh minimal 44px, teks status, dan kartu riwayat responsif.

## Verifikasi yang dijalankan

- Sebelum perubahan: 136 test / 598 assertion lulus.
- Pengujian awal pelanggaran dan akses: 30 test / 216 assertion lulus; kemudian ditambahkan pengujian akses Owner, escaping alasan, serta redirect/error dialog DQ.
- Hasil akhir seluruh suite, dijalankan berurutan: **168 test / 835 assertion lulus**.
- Cakupan: warning pertama, eskalasi lintas Juri/alasan, DQ langsung, validasi, form lama, submit berulang, status tertutup/finalized, penolakan akses/lintas kelas/event, privasi, escaping, preservation nilai, pengecualian progres, rollback, dan event setelah commit.
- Laravel Pint, kompilasi Blade, sintaks JavaScript, build Vite, dan `git diff --check` berhasil.
- Pemeriksaan visual browser pada 360/390/768/1024/1280/1440px belum dilakukan. Review langsung modal dan tampilan ponsel masih diperlukan.
- Build menampilkan peringatan opsional `fontaine` yang sudah ada sebelumnya; build tetap berhasil. Tidak ada dependency baru pada tahap ini.
- Pengujian stale/repeated request dan rollback sudah ada; stress test dengan proses database konkuren terpisah belum dijalankan.
- Satu percobaan verifikasi akhir terganggu karena dua proses PHPUnit memakai database uji yang sama secara bersamaan (DDL/lock conflict). Kegagalan tersebut berasal dari eksekusi pengujian, bukan perubahan schema aplikasi; seluruh suite diulang berurutan. Pengujian redirect validasi juga mempertahankan cookie sesi sebagaimana browser agar old input/error benar-benar dibaca dari sesi yang sama.

## Berkas utama

- Domain: `RecordViolationAction`, `ViolationRuleService`, dan `EventViolationQuery` pada `app/Domain/Judging/`.
- Data/otorisasi: model/factory `Violation`, dua enum pelanggaran, `ViolationPolicy`, relasi registration/Juri, morph map audit, migration baru.
- HTTP: controller/request pelanggaran Juri, controller riwayat Admin, integrasi controller lembar nilai/detail peserta/monitor Owner, serta route khusus peringatan dan DQ.
- UI: partial `judge/evaluations/partials/violation-actions`, komponen `violation-history`, halaman `admin/violations/index`, status peserta, dan progressive dialog pada `resources/js/app.js`.
- Tests: `ViolationWorkflowTest` dan `ViolationAccessTest` pada `tests/Feature/Judging/`.
- Kontrak terkait diperbarui pada `Schema.md`, `Architecture.md`, dan `Design.md`. Semantik PRD tidak diubah.

## Batas tahap dan tahap berikutnya

Ranking belum diimplementasikan. DQ segera dikeluarkan dari progres yang memakai filter `VERIFIED`, dan domain event tersedia untuk integrasi berikutnya; belum ada recalculation TOPSIS atau broadcast yang diklaim berjalan.

**Tahap 7 — Rata-rata Nilai, TOPSIS per Kelas, Ranking Provisional, Finalisasi & Hasil Akhir.** Input wajib mengecualikan DQ/draft dan memakai rata-rata submitted. Tahap ini juga menghubungkan perubahan penilaian/DQ ke recalculation provisional. Finalisasi harus menghitung ulang dari sumber, menyimpan snapshot, dan menjaga hasil final immutable. Aturan minimum/absensi Juri dan tie-break tetap pertanyaan bisnis terbuka; tidak boleh diam-diam ditentukan. Realtime dan sertifikat mengikuti Tahap 8.
