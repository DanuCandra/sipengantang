# Tahap 7 — Rata-rata, TOPSIS, Draw, Finalisasi & Hasil Resmi

Status: selesai diimplementasikan dan siap direview. Tahap 8 belum dimulai; menunggu persetujuan/revisi pengguna.

## Keputusan pengguna yang diterapkan

1. Minimum **3 Juri aktif** untuk finalisasi. Semua penugasan aktif wajib menyelesaikan evaluasi lengkap untuk semua peserta eligible. Provisional boleh memakai submitted yang tersedia, tetapi bukan hasil resmi dan tidak dipublikasikan.
2. **Juri absen dapat diganti** oleh Admin event terkait. Riwayat penugasan/draft dipertahankan; akses Juri lama ke kelas tersebut dicabut. Pengganti menilai sendiri dan jumlah Juri tidak dikurangi diam-diam. Juri yang sudah mempunyai submitted tidak dapat diganti melalui alur ini karena aturan penggantian setelah menilai sebagian belum diputuskan.
3. **Draw diselesaikan dengan lempar dadu nyata di lokasi.** Total dadu tertinggi menang; Admin mencatat urutan hasilnya hanya dalam kelompok yang benar-benar seri, dengan catatan dan checkbox konfirmasi. Jika lemparan masih seri, tunggu hasil undian yang menentukan urutan. Tidak ada simulasi dadu, pemilihan pemenang acak, perubahan skor, atau pengubahan peringkat peserta non-seri.

Keputusan terbaru ini menggantikan status OPEN/PROPOSED mengenai minimum Juri, ketidakhadiran, dan tie pada dokumen lama. `AGENTS.md`, PRD, Architecture, Schema, Design, serta kontrak algoritme telah diselaraskan. Kebijakan penggantian setelah submitted, koreksi hasil undian yang sudah dicatat, reopen kelas final, dan revisi skor tetap tidak diimplementasikan tanpa keputusan baru.

## Fitur tersedia

- Rata-rata per kriteria hanya dari evaluasi submitted valid milik penugasan aktif.
- Perhitungan TOPSIS terpusat, deterministik, per kelas; semua kriteria MVP BENEFIT dengan bobot snapshot kelas.
- Draft, pembayaran belum valid, pembatalan, DQ, dan penugasan yang telah diganti tidak masuk matriks ranking.
- Provisional tersimpan sebagai riwayat; perubahan nilai submitted/DQ setelah commit mengantrekan pembaruan lewat database queue. Job digabung per kelas dan idempoten berdasarkan hash sumber. Tombol hitung ulang HTTP tetap tersedia tanpa queue worker/Reverb.
- Halaman Admin membedakan PROVISIONAL/FINAL, menampilkan progres, hasil, dan detail rata-rata/jarak. Provisional tidak dapat dibuka publik.
- Hasil draw terikat snapshot nilai/Juri/peserta/kriteria/hadiah tertentu. Sumber berubah → keputusan lama tidak dipakai untuk finalisasi, tetapi riwayatnya tetap tersimpan. Keputusan yang sudah dicatat tidak dapat ditimpa.
- Finalisasi mengunci kelas, mengotorisasi Admin, memvalidasi kelengkapan, menghitung ulang dari raw submitted scores, menerapkan keputusan draw yang masih cocok, lalu menyimpan FINAL dan mengunci kelas secara atomik.
- Nilai TOPSIS asli tetap diarsipkan walaupun urutan resmi kelompok seri mengikuti lempar dadu. Final result menyimpan salinan bukti keputusan draw dan mempertahankan `is_tied=true`.
- Kelas final menolak penilaian, DQ, penugasan/penggantian Juri, edit informasi kelas, serta perubahan hadiah. Snapshot final/results tidak dapat diperbarui/dihapus lewat model. Tidak ada unfinalize.
- Hasil resmi publik dibaca dari snapshot tersimpan. Tidak ada perhitungan pada GET publik atau ketergantungan pada nama burung/peserta/hadiah live. Kontak privat, raw nilai Juri, matriks internal, dan catatan undian tidak dipublikasikan.
- Admin dapat menutup event secara eksplisit setelah semua kelas final/dibatalkan dan setidaknya satu kelas mempunyai hasil final. Event tidak otomatis selesai berdasarkan waktu; kelas baru ditolak pada event tertutup.

## Database dan audit

Empat migration baru, tanpa mengubah migration tahap yang sudah diterapkan:

Keempatnya sudah diterapkan ke database lokal (`APP_ENV=local`), tanpa reset, penghapusan data, atau seeding ulang.

```text
2026_10_21_222748_create_ranking_runs_table.php
2026_10_21_222750_create_ranking_results_table.php
2026_10_21_222752_create_ranking_tie_resolutions_table.php
2026_10_21_222754_add_replacement_history_to_judge_assignments_table.php
```

`ranking_runs` menyimpan versi algoritme, hash sumber, kriteria, input beserta raw submitted yang digunakan, seluruh matriks/ideal, waktu, dan finalizer. `ranking_results` menyimpan identitas publik historis, rata-rata, dua jarak, preferensi DECIMAL(16,10), rank, serta penanda seri. Rank memakai INT UNSIGNED agar tidak terpotong pada kuota kelas sampai 100.000. `ranking_tie_resolutions` mencatat urutan, Admin, waktu, dan catatan secara append-only. FK melindungi sejarah; tidak ada event_id redundan di registration.

Penggantian mengunci kelas, penugasan, dan akun pengganti. Draw/finalisasi/recalculation mengunci kelas, selaras dengan penulisan nilai dan DQ; perhitungan kelengkapan dilakukan setelah lock. Activity log baru: `JUDGE_REPLACED`, `DRAW_RESULT_RECORDED`, `RESULT_FINALIZED`, dan `EVENT_COMPLETED`. `ResultFinalized` hanya membawa ID dan baru dikirim setelah commit. Sertifikat/realtime akan memakai batas integrasi ini pada Tahap 8, bukan job kosong yang seolah-olah telah membuat PDF.

## Presisi dan edge case

Detail rumus, precision, dan fixture hitung tangan terdapat di `TOPSIS.md`. Tidak membulatkan nilai antara. Preferensi yang sama pada presisi penyimpanan 10 desimal membentuk draw; tampilan 4 desimal tidak dipakai untuk membandingkan pemenang. Satu peserta mendapat rank 1 dengan preferensi netral 0,5; alternatif identik ditandai draw, dan nol peserta eligible menolak finalisasi. ID peserta hanya menstabilkan tampilan dalam seri, bukan tie-break.

## Berkas utama yang berubah

- `app/Domain/Ranking/`: calculator, input builder, ranking service, guard final, action recalculation/draw/finalization.
- `app/Domain/Judging/Actions/ReplaceAbsentJudgeAction.php`, scope aktif assignment, policy/query/progres Juri yang memperhitungkan penggantian.
- Model/enum/factory ranking, event `ResultFinalized`, listener `QueueRankingRecalculation`, job `RecalculateProvisionalRanking`.
- Controller/request Admin untuk ranking/penggantian/penutupan event; controller publik khusus FINAL.
- Penguncian perubahan kelas/hadiah, penolakan kelas baru setelah event ditutup; route Admin/publik terkait.
- Komponen `ranking-results`, halaman Admin ranking, halaman hasil resmi publik, integrasi detail kelas/event/penugasan.
- `tests/Unit/TopsisCalculatorTest.php`, `tests/Feature/Ranking/`, dan `tests/Concerns/CreatesRankingFixtures.php`.
- Dokumentasi utama, `AGENTS.md`, `TOPSIS.md`, dan kontrak tahap ini.

## Panduan review

1. Login Admin event yang ditugaskan, buka **Event → Kelas → Kelola Juri & penjurian**. Pastikan minimal tiga Juri aktif ditugaskan.
2. Untuk Juri yang absen dan belum submit, pilih **Ganti Juri absen**, isi email akun pengganti yang aktif dan alasan. Jika belum ada akun, buat akun melalui alur yang sudah tersedia lalu gunakan sebagai pengganti. Cek riwayat penugasan lama dan pastikan akun lama tidak bisa menilai kelas tersebut.
3. Minta tiap Juri aktif mengirim seluruh kriteria peserta yang terverifikasi. Draft tidak ikut perhitungan.
4. Buka **Ranking & finalisasi → Hitung ulang dari database**. Periksa label sementara, progres, peringkat, dan detail rata-rata/jarak. Uji tombol ini ketika worker belum berjalan.
5. Coba finalisasi saat ada Juri/peserta eligible belum dinilai. Server harus menolak tanpa menambah FINAL.
6. Untuk peserta dengan koefisien sama, lakukan lempar dadu di lokasi. Di panel draw, pilih urutan peserta sesuai total tertinggi ke terendah, catat hasilnya, lalu konfirmasi. Coba duplikasi peserta/ID di luar kelompok dan pastikan ditolak.
7. Coba mengganti keputusan draw yang sudah tersimpan: harus ditolak. Jika ada perubahan sumber sebelum final, hitung ulang dan tinjau kelompok draw baru; keputusan lama bukan izin mengubah ranking baru.
8. Ketik **FINALISASI** pada dialog. Periksa status FINAL, urutan hasil undian, penguncian kelas, dan tautan hasil resmi publik.
9. Coba submit nilai, DQ, penggantian Juri, edit kelas, serta perubahan hadiah setelah final. Semua ditolak server; nilai/riwayat lama tetap tersimpan.
10. Buka hasil publik tanpa login. Pastikan hanya snapshot hasil resmi/identitas publik/hadiah yang tampil, tanpa raw nilai, kontak, atau catatan undian.
11. Setelah seluruh kelas final/dibatalkan, buka event dan pilih **Selesaikan event**, lalu ketik **SELESAI**. Periksa agenda completed dan hasil resmi yang tetap tersedia.

## Verifikasi

- Baseline sebelum perubahan: 168 test / 835 assertion lulus.
- Suite terarah algoritme/ranking: 36 test / 198 assertion lulus sebelum penambahan tes integrasi dan isolasi draw di tengah ranking.
- Tes integrasi terakhir: 4 test / 30 assertion lulus.
- Suite akhir: **209 test / 1.077 assertion lulus**, termasuk semua tes tahap sebelumnya, isolasi kelompok draw di tengah ranking, dan penguncian kelas/hadiah final.
- Build Vite berhasil; peringatan optional `fontaine` yang telah ada sebelumnya tetap muncul. Tidak ada dependency baru.
- Kompilasi Blade, Laravel Pint, pemeriksaan sintaks JavaScript, serta `git diff --check` berhasil. Audit Composer tidak menemukan advisory atau dependency abandoned.
- Uji awal sempat gagal karena membandingkan urutan key JSON in-memory dengan urutan canonical MySQL, penggunaan `sole()` pada dua hasil, serta ekspektasi jumlah job yang tidak sesuai unique/coalesced dispatch. Fixture/assertion diperbaiki tanpa menghapus tes; seluruh suite diulang berurutan.
- Pemeriksaan visual browser pada 360/390/768/1024/1280/1440px belum dilakukan. Review langsung dialog, panel draw, dan tampilan ponsel masih diperlukan.
- Tidak menjalankan stress test konkuren terpisah. Lock, stale-source guard, request berulang, rollback, immutable snapshot, dan otorisasi negatif diuji secara integrasi.

## Tahap berikutnya

**Tahap 8 — Realtime dan Piagam/Sertifikat Pemenang.** Integrasi Reverb/Echo, status koneksi/fallback polling, broadcast payload privat/publik, serta queue pembuatan dan unduhan sertifikat dari FINAL. Format tanda tangan/stempel masih memerlukan aset/keputusan pengguna. Belum dikerjakan pada Tahap 7; berhenti dahulu untuk review.
