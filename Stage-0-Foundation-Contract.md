# Tahap 0 — Foundation Contract GantangScore

**Status:** APPROVED — TAHAP 1 DIMULAI  
**Tanggal:** 16 September 2026  
**Produk:** GantangScore — Sistem Penilaian Gantangan  
**Scope:** Penyelarasan produk, arsitektur, schema, desain, keputusan teknis, dan decision gate sebelum implementasi.

---

## 1. Tujuan

Dokumen ini menjadi keluaran resmi Tahap 0. Isinya tidak mengimplementasikan fitur, migration, atau tampilan. Dokumen ini menetapkan baseline yang akan digunakan pada Tahap 1 setelah direview.

Tahap berikutnya tidak boleh menganggap keputusan berstatus `PROPOSED` sebagai aturan final sebelum disetujui.

---

## 2. Urutan Otoritas Dokumen

Jika terdapat konflik, gunakan urutan berikut:

1. Instruksi terbaru pengguna.
2. Keputusan berstatus `APPROVED` di dokumen Tahap 0 ini.
3. `PRD.md` untuk requirement dan aturan bisnis.
4. `Design.md` untuk UI/UX dan perilaku responsif.
5. `Architecture.md` untuk struktur teknis dan boundary.
6. `Schema.md` untuk kontrak database.
7. Konvensi kode yang sudah terbukti di aplikasi.
8. Dokumentasi resmi versi framework/package yang terpasang.

Aturan bisnis yang belum diputuskan harus tetap menjadi decision gate. Implementasi tidak boleh membuat asumsi diam-diam.

---

## 3. Identitas Produk

| ID | Keputusan | Status |
| --- | --- | --- |
| S0-PROD-01 | Nama produk utama adalah **GantangScore**. | APPROVED — `AGENTS.md` terbaru |
| S0-PROD-02 | Kepanjangan produk adalah **Sistem Penilaian Gantangan**. | APPROVED — instruksi pengguna |
| S0-PROD-03 | `SIPENGANTANG` tetap menjadi nama repository/proyek teknis; merek yang tampil kepada pengguna adalah `GantangScore`. | APPROVED |
| S0-PROD-04 | Bahasa UI utama adalah Bahasa Indonesia dengan locale `id-ID`. | APPROVED — dokumen utama |
| S0-PROD-05 | Timezone aplikasi adalah `Asia/Jakarta`; UI menampilkan WIB. | APPROVED — dokumen utama |

Working copy empat dokumen awal tetap dipertahankan selama review. Setelah kontrak ini disetujui, penyebutan merek lama akan diganti secara konsisten tanpa mengubah makna requirement.

---

## 4. Prinsip Produk yang Dikunci

1. Satu event memiliki banyak kelas dan ranking selalu dihitung per kelas.
2. Pendaftaran resmi berasal dari registration berstatus `VERIFIED`.
3. Hanya evaluasi juri berstatus `SUBMITTED` yang masuk perhitungan.
4. Nilai juri dirata-ratakan per peserta dan per kriteria sebelum TOPSIS.
5. Peserta `DISQUALIFIED` tidak masuk ranking, tetapi skor dan pelanggarannya tidak dihapus.
6. Ranking final tidak dapat diedit manual.
7. Finalisasi selalu menghitung ulang dari sumber data, bukan mempromosikan provisional run.
8. Hasil final disimpan sebagai snapshot yang dapat direproduksi.
9. Database adalah source of truth; Reverb hanya menyampaikan perubahan.
10. Global role tidak cukup untuk memberikan akses. Event, kelas, registration, dan file harus diperiksa scope-nya di server.
11. Bukti pembayaran bersifat privat dan setiap percobaan upload disimpan sebagai row terpisah.
12. Aplikasi tetap berfungsi melalui HTTP ketika queue broadcast atau Reverb tidak tersedia.

---

## 5. Baseline Teknologi

| Area | Keputusan Tahap 0 | Status |
| --- | --- | --- |
| Runtime | PHP 8.5 dan Laravel 13 sesuai instalasi proyek saat ini. | APPROVED |
| Arsitektur | Modular monolith dengan satu aplikasi Laravel dan satu database utama. | APPROVED |
| Rendering | Server-rendered Laravel Blade. Tidak menggunakan SPA, Inertia, atau Livewire. | APPROVED |
| Styling | Tailwind CSS 4 dengan design tokens terpusat. | APPROVED |
| Interaksi lokal | Progressive JavaScript; Alpine.js 3 boleh dipasang untuk drawer, modal, dropdown, wizard, dan state lokal. | TECHNICAL BASELINE |
| Authentication | Laravel session authentication dengan controller, Form Request, Password Broker, middleware, dan Blade milik aplikasi. | TECHNICAL BASELINE |
| Database produksi | MySQL. SQLite boleh dipakai untuk unit test cepat, tetapi bukan satu-satunya database pada verification gate. | TECHNICAL BASELINE |
| Queue MVP | Laravel database queue. | APPROVED |
| Realtime | Laravel Reverb + Laravel Echo melalui installer resmi Laravel. | APPROVED |
| PDF | `barryvdh/laravel-dompdf` 3.1+ dengan template Blade PDF terpisah dan remote asset dinonaktifkan. | TECHNICAL BASELINE |
| Storage | Disk publik untuk banner/foto burung; disk privat untuk bukti pembayaran dan dokumen terotorisasi. | APPROVED |
| Testing | PHPUnit 12 sesuai proyek; unit, feature, authorization, algorithm, broadcast, dan document tests. | APPROVED |

### 5.1 Alasan Authentication Tidak Menggunakan Starter Kit

Starter kit resmi Laravel 13 saat ini menggunakan Livewire atau Inertia, sedangkan kontrak aplikasi menetapkan Blade tanpa Livewire/SPA. Versi stabil Laravel Fortify yang tersedia saat pemeriksaan belum mendeklarasikan kompatibilitas `illuminate/support` 13.x.

Karena itu autentikasi dibangun menggunakan fasilitas framework Laravel yang stabil:

- session guard,
- `Auth::attempt`,
- session regeneration,
- password broker,
- password reset token,
- middleware `auth` dan `guest`,
- login throttling,
- Blade forms dengan CSRF,
- feature dan authorization tests.

Email reset password adalah bagian keamanan autentikasi, bukan fitur notifikasi event yang berada di luar MVP.

### 5.2 Aturan Dependensi

- Dependensi baru hanya dipasang pada tahap yang membutuhkannya.
- Versi dan kompatibilitas harus diperiksa kembali sebelum instalasi.
- Tidak menambahkan Redis, Horizon, SPA framework, payment gateway, atau notification provider pada MVP.
- Package harus memiliki alasan domain yang jelas dan tidak menggantikan fitur framework tanpa manfaat nyata.

---

## 6. Kontrak Desain

Referensi visual utama:

`https://uupm.cc/demo/veterinary-clinic`

Referensi UI/UX Pro Max digunakan sebagai pola, bukan sebagai template yang disalin secara literal.

### 6.1 Pola Referensi yang Dipertahankan

- Hierarki landing yang jelas: hero, trust signal, fitur, social proof, dan CTA.
- Tampilan ramah, mudah dipercaya, dan memiliki whitespace yang cukup.
- Card beradius lembut, shadow ringan, serta informasi yang mudah dipindai.
- Navigasi sederhana dan CTA yang terlihat jelas.
- Responsive layout, feedback interaksi, dan accessibility-first.
- Friendly imagery yang diadaptasi menjadi burung, gantangan, juri, peserta, dan suasana lomba.

### 6.2 Adaptasi GantangScore

- Palet klinik biru/oranye tidak disalin. GantangScore memakai hijau alam `#276453` dan emas `#D9A441` sebagai identitas kompetisi.
- Claymorphism hanya menjadi aksen ringan pada landing page. Dashboard, tabel, monitoring, pembayaran, dan penjurian menggunakan flat/soft UI yang lebih tenang.
- Landing page boleh emosional dan visual; dashboard harus cepat, padat, dan operasional.
- Inter digunakan untuk keterbacaan form, angka, tabel, dan mobile scoring.
- Lucide menjadi sistem ikon utama; emoji tidak digunakan sebagai action icon.
- Motion dibatasi 150–250 ms dan tidak boleh mengubah fokus, posisi scroll, atau mengganggu proses menilai.
- Status selalu mempunyai teks/ikon, tidak hanya warna.
- Layar scoring juri dan registration wizard diprioritaskan untuk penggunaan satu tangan di lebar 360 px.
- Jika referensi visual bertentangan dengan aksesibilitas atau kejelasan operasional, kejelasan dan aksesibilitas menang.

### 6.3 Aset Visual

- Development boleh memakai placeholder yang jelas.
- Production tidak boleh menampilkan metrik palsu.
- Foto burung/event harus memiliki izin penggunaan dan ukuran teroptimasi.
- Tidak menyalin logo, foto, copy, atau aset proprietary dari website referensi.

---

## 7. Boundary Domain dan Source of Truth

```text
Identity
  users → user_roles → participant_profiles

Event
  events → event_admins → competition_classes
                         → criteria snapshots
                         → prizes

Participation
  birds → registrations → payment_submissions

Judging
  judge_assignments → judge_evaluations → judge_scores
                                      → violations

Ranking
  submitted scores → averages → TOPSIS → ranking_runs → ranking_results

Documents
  verified registration → registration proof
  final ranking result → certificate
```

### 7.1 Application Boundary

- Controller: request, authorization, action invocation, response.
- Form Request: input validation dan request-level authorization bila sesuai.
- Policy/Gate: resource scope dan capability.
- Action: satu use case bisnis dan transaction boundary.
- Service: logika domain reusable/pure, terutama TOPSIS dan violation rules.
- Job: pekerjaan durable, retryable, idempotent.
- Event: fakta domain yang sudah terjadi dan dikirim setelah commit.
- Blade: presentasi data yang sudah dipersiapkan; tidak melakukan query.

---

## 8. Baseline Schema dan Revisi yang Direkomendasikan

Struktur inti pada `Schema.md` dapat digunakan. Revisi berikut menjadi baseline teknis setelah review:

| ID | Revisi | Status |
| --- | --- | --- |
| S0-DB-01 | Tambahkan `algorithm` dan `algorithm_version` pada `ranking_runs`; default `TOPSIS` dan `1.0`. | RECOMMENDED |
| S0-DB-02 | Hanya ranking run yang berhasil disimpan sebagai snapshot resmi. Kegagalan sebelum persist dicatat di `failed_jobs` dan technical log, bukan row snapshot setengah jadi. | RECOMMENDED |
| S0-DB-03 | `created_by` pada FINAL run berarti admin yang memulai finalisasi; provisional otomatis boleh `NULL`. | RECOMMENDED |
| S0-DB-04 | Gunakan morph map alias stabil untuk `activity_logs.subject_type` agar refactor namespace tidak merusak audit. | RECOMMENDED |
| S0-DB-05 | Enum PHP menggunakan case TitleCase dengan value database uppercase, misalnya `case Verified = 'VERIFIED';`. | RECOMMENDED |
| S0-DB-06 | Index migration harus menghindari index tunggal yang sudah tercakup oleh composite index atau foreign-key index. | RECOMMENDED |
| S0-DB-07 | Finalisasi, pembayaran, DQ, warning escalation, submit skor, dan quota check menggunakan transaction + row lock. | APPROVED |
| S0-DB-08 | Tidak menambahkan `event_id` pada registration karena sudah dapat ditelusuri melalui competition class. | APPROVED |
| S0-DB-09 | Tidak menggunakan FLOAT/DOUBLE untuk uang atau hasil TOPSIS yang disimpan. | APPROVED |
| S0-DB-10 | Tidak menambahkan tabel master jenis burung, waiting list, revision score, appeal, atau QR sebelum requirement disetujui. | APPROVED |

### 8.1 Catatan Ranking Failure

`ranking_runs` adalah histori perhitungan yang reproducible, bukan log eksekusi job. Karena itu snapshot parsial atau gagal tidak boleh menjadi official run. Kegagalan provisional mempertahankan last successful run dan menampilkan stale timestamp bila diperlukan. Finalisasi gagal harus rollback seluruh transaction.

---

## 9. Security Baseline

1. Semua mutasi menggunakan CSRF-protected HTTP request.
2. Authentication tidak pernah menggantikan authorization.
3. Semua route nested memvalidasi rantai event → class → resource untuk mencegah IDOR.
4. Admin hanya mengakses event melalui owner relation atau `event_admins`.
5. Juri hanya menilai melalui `judge_assignments`.
6. Participant hanya mengakses burung, registration, bukti, dan piagam miliknya.
7. Upload divalidasi melalui MIME, extension yang diizinkan, dan batas 5 MB.
8. Nama file storage dibuat sistem; nama asli tidak menjadi path.
9. Bukti pembayaran tidak mempunyai predictable public URL.
10. Sort/filter identifier menggunakan allow-list.
11. Model tidak menerima `$request->all()`.
12. Login dan password reset diberi rate limit.
13. Public broadcast tidak mengandung email, telepon, alamat, bukti pembayaran, atau catatan internal.
14. Event/broadcast yang bergantung pada state database dikirim setelah commit.

---

## 10. Testing dan Verification Gate

Setiap vertical slice harus mempunyai test positif dan negatif yang sebanding dengan risikonya.

Minimum gate proyek:

- Unit test untuk state/domain service.
- Feature test untuk HTTP flow.
- Authorization test untuk role dan resource scope.
- Constraint test untuk unique/FK penting.
- MySQL-backed test untuk transaction, locking, dan perilaku schema yang tidak identik dengan SQLite.
- Golden fixture TOPSIS dengan numeric tolerance yang eksplisit.
- Test satu peserta, alternatif identik, tie, draft exclusion, dan DQ exclusion.
- Broadcast channel authorization dan public payload privacy.
- PDF smoke test dan visual verification pada tahap dokumen.
- Responsive verification pada 360, 390, 768, 1024, 1280, dan 1440 px.
- `vendor/bin/pint --dirty --format agent` setelah perubahan PHP.
- Narrow tests dijalankan terlebih dahulu sebelum suite yang lebih luas.

---

## 11. Decision Register yang Memerlukan Review

Keputusan berikut tidak menghalangi pembuatan fondasi umum, tetapi harus disetujui sebelum tahap terkait.

| ID | Pertanyaan | Rekomendasi aman | Dibutuhkan sebelum |
| --- | --- | --- | --- |
| D-01 | Apakah satu burung boleh mengikuti beberapa kelas dalam event yang sama? | Boleh, selama jenis burung kompatibel; satu registration tetap hanya untuk satu kelas. | Tahap 3 |
| D-02 | Registration mana yang menghabiskan kuota? | Hitung semua status aktif selain `CANCELLED` dan `PAYMENT_REJECTED`; kebijakan kedaluwarsa pembayaran ditunda sampai ada aturan batas waktu. | Tahap 3 |
| D-03 | Cara pemberian nomor gantangan? | Manual oleh admin; unik per kelas setelah nomor diisi. | Tahap 3 |
| D-04 | Minimum juri per kelas? | Minimum 3 untuk finalisasi; jumlah disimpan sebagai aturan class bila disetujui. | Tahap 4/5 |
| D-05 | Jika juri tidak hadir? | Admin mengganti assignment sebelum penilaian dimulai; jangan mengurangi denominator diam-diam. | Tahap 5 |
| D-06 | Bolehkah nilai SUBMITTED dikoreksi? | Tidak pada MVP. Perubahan masa depan wajib memakai revision history. | Tahap 5 |
| D-07 | Bolehkah DQ dibatalkan? | Tidak pada MVP. Mekanisme review/appeal ditambahkan hanya jika disetujui. | Tahap 5 |
| D-08 | Aturan tie TOPSIS? | Shared rank dan `is_tied=true`; finalisasi diblokir jika tie menyentuh batas hadiah sampai aturan tie-break diputuskan. | Tahap 6 |
| D-09 | Apa yang dilihat publik saat event berlangsung? | Progres penjurian saja; nilai/ranking provisional tetap internal admin. | Tahap 7/8 |
| D-10 | Format piagam? | Template dasar tanpa tanda tangan digital; logo/tanda tangan/stempel menunggu aset resmi. | Tahap 7 |
| D-11 | Hadiah tingkat event di luar kelas? | Tidak untuk MVP; hadiah tetap per kelas. | Tahap 2/7 |

Status seluruh D-01 sampai D-11 adalah `PROPOSED` sampai direview pengguna.

---

## 12. Konflik dan Risiko yang Sudah Ditemukan

| ID | Temuan | Resolusi Tahap 0 |
| --- | --- | --- |
| C-01 | Nama repository SIPENGANTANG berbeda dari identitas produk di dokumen. | GantangScore menjadi merek canonical; SIPENGANTANG tetap nama teknis repository. |
| C-02 | Project skeleton memakai SQLite, requirement memakai MySQL. | MySQL menjadi target runtime; SQLite tidak boleh menjadi satu-satunya verification environment. |
| C-03 | Blade diwajibkan, starter kit resmi Laravel 13 memakai Livewire/Inertia. | Gunakan auth session Laravel dengan custom Blade. |
| C-04 | Schema mengizinkan ranking run `FAILED`, tetapi snapshot diwajibkan non-null. | Persist official run hanya setelah kalkulasi sukses; failure masuk log/failed job. |
| C-05 | Referensi Veterinary Clinic lebih ramah/playful daripada dashboard kompetisi. | Ambil hierarki, trust, dan soft UI; gunakan palet serta density GantangScore. |
| C-06 | PRD menunda email notification, sedangkan forgot-password membutuhkan email. | Email keamanan autentikasi dibedakan dari notifikasi event. |
| C-07 | Progress denominator bergantung pada aturan juri absen. | Perhitungan dipusatkan dan tidak difinalkan sampai D-04/D-05 disetujui. |
| C-08 | Public provisional score/ranking belum diputuskan. | Safe default yang diusulkan adalah progress only. |

---

## 13. Scope Tahap 1 Setelah Approval

Tahap 1 hanya mencakup:

1. Konfigurasi MySQL development/test.
2. Authentication session berbasis Blade.
3. User status, multi-role, participant profile dasar.
4. Policy/gate dan role redirect baseline.
5. Struktur route per audience.
6. Design tokens, komponen UI inti, guest layout, dan dashboard shell.
7. PHPUnit/factory/seeder baseline.
8. Security dan authorization tests untuk fondasi.

Tahap 1 tidak mencakup event CRUD, registration, payment, judging, TOPSIS, PDF, atau Reverb.

---

## 14. Acceptance Criteria Tahap 0

- [x] PRD, Design, Architecture, dan Schema dibaca penuh.
- [x] Nama produk canonical ditetapkan.
- [x] Referensi UI/UX Pro Max dicatat dan aturan adaptasinya dijelaskan.
- [x] Kondisi aktual project dan versi dependency diperiksa.
- [x] Stack teknis yang tidak cocok dihindari.
- [x] Source of truth dan domain boundary disepakati dari dokumen.
- [x] Schema risk dan rekomendasi revisi dicatat.
- [x] Security dan testing baseline ditentukan.
- [x] Open business questions dipetakan ke tahap yang membutuhkannya.
- [x] Scope Tahap 1 dibatasi agar dapat direview sebagai vertical foundation slice.
- [ ] Pengguna menyetujui atau merevisi D-01 sampai D-11.
- [x] Pengguna menyetujui dimulainya Tahap 1.

---

## 15. Review Gate

Tahap 0 dianggap disetujui untuk fondasi Tahap 1 apabila:

1. Branding GantangScore diterima.
2. Baseline teknologi dan adaptasi desain diterima.
3. Rekomendasi schema diterima atau diberi revisi.
4. Pertanyaan D-01 sampai D-11 tetap dicatat dan diputuskan sebelum tahap yang bergantung padanya.
5. Pengguna secara eksplisit meminta mulai Tahap 1.

Review gate fondasi telah dilewati melalui permintaan eksplisit pengguna untuk mengerjakan Tahap 1. Pertanyaan bisnis terbuka belum dianggap disetujui secara otomatis.
