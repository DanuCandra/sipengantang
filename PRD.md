# PRODUCT REQUIREMENTS DOCUMENT

## Sistem Penjurian Kompetisi Gantangan Burung dengan Algoritma TOPSIS

**Platform:** Web Application  
**Backend:** Laravel  
**Frontend:** Laravel Blade  
**Database:** MySQL  
**Realtime:** Laravel Reverb + Laravel Echo/WebSocket  
**Metode Pengambilan Keputusan:** TOPSIS  
**Pembayaran:** Transfer/manual, diverifikasi admin  
**Role:** Owner, Admin, Juri, Peserta

---

# 1. Problem Statement

Kompetisi gantangan burung memiliki proses yang cukup kompleks karena melibatkan pendaftaran peserta, verifikasi pembayaran, pembagian kelas, proses penjurian oleh beberapa juri, penanganan pelanggaran, perhitungan nilai, penentuan pemenang, publikasi hasil, hingga pemberian piagam.

Jika proses tersebut dilakukan secara manual, beberapa masalah dapat terjadi:

- Nilai juri harus direkap secara manual.
- Risiko salah perhitungan nilai cukup tinggi.
- Rekap dari beberapa juri memakan waktu.
- Penentuan peringkat dapat dianggap kurang transparan.
- Sulit mengetahui progres penjurian secara realtime.
- Data peserta dan burung tersebar di banyak tempat.
- Bukti pembayaran harus dicek secara manual tanpa sistem terpusat.
- Data pelanggaran dan diskualifikasi sulit dilacak.
- Peserta harus menunggu pengumuman panitia untuk mengetahui hasil.
- Riwayat lomba dan pemenang sulit didokumentasikan.
- Admin harus membuat bukti pendaftaran dan piagam secara manual.

### Pihak yang paling terdampak

**Panitia/Admin**

Harus mengelola peserta, pembayaran, kelas, juri, jadwal, hadiah, hasil, dan dokumen lomba secara manual.

**Juri**

Harus memberikan penilaian dan menyerahkan nilai kepada panitia untuk dihitung kembali.

**Peserta**

Tidak mempunyai satu tempat untuk melihat status pendaftaran, pembayaran, informasi event, progres pertandingan, hasil lomba, dan piagam.

**Owner/Penyelenggara**

Sulit memonitor banyak event serta memastikan proses penjurian berjalan secara konsisten.

### Solusi

Membangun sistem terpusat yang memungkinkan:

`Owner membuat event → Admin mengelola event → Peserta mendaftar → Admin memverifikasi → Juri menilai → Nilai juri dirata-rata → TOPSIS menghitung ranking → Hasil dipublikasikan → Pemenang memperoleh piagam.`

---

# 2. Target User + Persona

## Target User

Sistem memiliki empat tipe pengguna utama:

1. Owner/Penyelenggara
2. Admin Event
3. Juri
4. Peserta

Pengunjung umum juga dapat menggunakan landing page tanpa login untuk melihat informasi event dan hasil lomba.

---

## Persona 1 — Admin Event

**Nama contoh:** Andi  
**Peran:** Panitia lomba burung  
**Kemampuan teknis:** Dasar–menengah  
**Device utama:** Laptop dan smartphone

### Kebutuhan

- Membuat dan mengelola kelas lomba.
- Melihat peserta yang mendaftar.
- Memeriksa bukti pembayaran.
- Memverifikasi peserta.
- Menentukan juri.
- Mengelola hadiah.
- Mengetahui progres penjurian.
- Melihat hasil TOPSIS.
- Mengumumkan pemenang.

### Pain Point

- Rekap peserta masih menggunakan spreadsheet.
- Bukti pembayaran dikirim melalui chat.
- Nilai harus dikumpulkan dari beberapa juri.
- Ranking dihitung secara manual.
- Pengumuman pemenang membutuhkan waktu.

### Tujuan

Seluruh proses event dapat dikelola melalui satu dashboard.

---

## Persona 2 — Peserta

**Nama contoh:** Budi  
**Peran:** Pemilik burung lomba  
**Device utama:** Smartphone

### Kebutuhan

- Melihat event lomba.
- Melihat kelas yang tersedia.
- Mengetahui biaya pendaftaran.
- Mengetahui hadiah.
- Mendaftarkan burung.
- Mengunggah bukti pembayaran.
- Melihat apakah pembayaran sudah diverifikasi.
- Mendapatkan bukti pendaftaran.
- Melihat progres perlombaan.
- Melihat hasil dan peringkat.
- Mengunduh piagam apabila menjadi juara.

### Pain Point

- Informasi lomba sering tersebar melalui poster/chat.
- Tidak mengetahui status verifikasi pembayaran.
- Harus bertanya kepada panitia mengenai hasil.
- Bukti pendaftaran tidak terdokumentasi dengan baik.

---

# 3. Goals dan Non-Goals

## Goals

### G1 — Digitalisasi proses kompetisi

Seluruh alur kompetisi dari pendaftaran hingga pengumuman pemenang berada dalam satu sistem.

### G2 — Penilaian yang konsisten

Semua juri menggunakan lima kriteria yang sama:

| Kriteria | Bobot |
|---|---:|
| Irama Lagu | 40% |
| Durasi Kerja / Kestabilan | 20% |
| Volume | 20% |
| Gaya | 10% |
| Fisik | 10% |

Setiap kriteria diberi nilai **1–10**.

---

### G3 — Penentuan ranking menggunakan TOPSIS

Nilai dari seluruh juri untuk setiap kriteria dirata-rata.

Hasil rata-rata kemudian digunakan sebagai matriks keputusan TOPSIS.

Ranking dihitung **per kelas lomba**, bukan secara global seluruh event.

---

### G4 — Transparansi proses pertandingan

Pengunjung dapat melihat event:

- Akan datang
- Sedang berlangsung
- Telah selesai

Untuk event yang sedang berlangsung tersedia informasi progres penjurian secara realtime.

---

### G5 — Pengelolaan multi-event

Owner dapat memiliki dan mengelola banyak event.

Satu event dapat mempunyai lebih dari satu admin.

---

### G6 — Multi-kelas

Satu event dapat mempunyai banyak kelas.

Contoh:

**Event Gantangan Albab Cup**

- Murai Batu Kelas Utama
- Murai Batu Kelas Eksekutif
- Kacer Kelas A
- Kacer Kelas B
- Cucak Hijau Kelas Utama

Masing-masing kelas dapat memiliki:

- Jenis burung
- Nama kelas
- Biaya pendaftaran
- Jadwal
- Kuota
- Hadiah berbeda
- Juri berbeda
- Peserta berbeda
- Ranking sendiri

---

## Non-Goals MVP

Fitur berikut tidak termasuk MVP:

- Payment gateway otomatis.
- WhatsApp notification.
- Email notification.
- Mobile application Android/iOS.
- AI untuk menilai suara burung.
- Computer vision untuk menilai fisik/gaya.
- Live streaming video.
- Integrasi rekening bank.
- Marketplace jual beli burung.
- Sistem tiket QR otomatis.
- Multi-language.
- Sistem afiliasi/referral.

---

# 4. User Stories

## Owner

### US-OWN-01

Sebagai **Owner**, saya ingin membuat event baru supaya kompetisi dapat dikelola melalui sistem.

### US-OWN-02

Sebagai **Owner**, saya ingin memilih admin dari akun yang sudah tersedia supaya admin tersebut dapat mengelola event tertentu.

### US-OWN-03

Sebagai **Owner**, saya ingin membuat akun admin baru supaya orang yang belum memiliki akun dapat menjadi admin event.

### US-OWN-04

Sebagai **Owner**, saya ingin menambahkan lebih dari satu admin ke suatu event supaya pengelolaan event dapat dilakukan bersama.

### US-OWN-05

Sebagai **Owner**, saya ingin melihat seluruh event supaya saya dapat memonitor penyelenggaraan lomba.

---

# Admin

### US-ADM-01

Sebagai **Admin**, saya ingin mengelola informasi event supaya peserta mendapatkan informasi lomba yang lengkap.

### US-ADM-02

Sebagai **Admin**, saya ingin membuat beberapa kelas dalam satu event supaya berbagai kategori lomba dapat dilaksanakan.

### US-ADM-03

Sebagai **Admin**, saya ingin menentukan biaya masing-masing kelas supaya setiap kelas dapat memiliki harga pendaftaran berbeda.

### US-ADM-04

Sebagai **Admin**, saya ingin menentukan hadiah untuk setiap peringkat supaya peserta mengetahui hadiah yang diperebutkan.

### US-ADM-05

Sebagai **Admin**, saya ingin menambahkan juri supaya juri dapat melakukan penilaian.

### US-ADM-06

Sebagai **Admin**, saya ingin menempatkan juri pada kelas tertentu supaya juri hanya dapat menilai kelas yang menjadi tanggung jawabnya.

### US-ADM-07

Sebagai **Admin**, saya ingin melihat bukti pembayaran peserta supaya saya dapat melakukan verifikasi.

### US-ADM-08

Sebagai **Admin**, saya ingin menyetujui atau menolak pembayaran supaya hanya peserta yang sudah diverifikasi yang dapat mengikuti lomba.

### US-ADM-09

Sebagai **Admin**, saya ingin melihat progres penjurian secara realtime supaya saya mengetahui proses lomba sudah sampai mana.

### US-ADM-10

Sebagai **Admin**, saya ingin melihat hasil TOPSIS supaya saya dapat mengetahui ranking setiap kelas.

### US-ADM-11

Sebagai **Admin**, saya ingin memfinalisasi hasil supaya hasil resmi tidak berubah setelah diumumkan.

---

# Juri

### US-JUR-01

Sebagai **Juri**, saya ingin melihat kelas yang ditugaskan kepada saya supaya saya hanya menilai kompetisi yang menjadi tanggung jawab saya.

### US-JUR-02

Sebagai **Juri**, saya ingin memberikan nilai 1–10 untuk setiap kriteria supaya performa setiap burung dapat dinilai.

### US-JUR-03

Sebagai **Juri**, saya ingin menyimpan penilaian sementara supaya nilai tidak hilang apabila belum selesai menilai.

### US-JUR-04

Sebagai **Juri**, saya ingin mengirim nilai final supaya nilai tersebut masuk ke perhitungan.

### US-JUR-05

Sebagai **Juri**, saya ingin memberikan peringatan kepada peserta supaya pelanggaran dapat tercatat.

### US-JUR-06

Sebagai **Juri**, saya ingin mendiskualifikasi peserta dengan mencatat alasannya supaya pelanggaran berat dapat ditangani.

---

# Peserta

### US-PST-01

Sebagai **Peserta**, saya ingin melihat event yang akan datang supaya saya dapat memilih lomba yang ingin diikuti.

### US-PST-02

Sebagai **Peserta**, saya ingin melihat kelas, biaya, lokasi, jadwal, dan hadiah supaya saya dapat menentukan kelas yang akan diikuti.

### US-PST-03

Sebagai **Peserta**, saya ingin mendaftarkan burung saya supaya dapat mengikuti lomba.

### US-PST-04

Sebagai **Peserta**, saya ingin mengunggah foto burung supaya profil burung dapat ditampilkan.

### US-PST-05

Sebagai **Peserta**, saya ingin mengunggah bukti pembayaran supaya admin dapat melakukan verifikasi.

### US-PST-06

Sebagai **Peserta**, saya ingin mengetahui status pembayaran supaya saya mengetahui apakah pendaftaran sudah diterima.

### US-PST-07

Sebagai **Peserta**, saya ingin mencetak bukti pendaftaran setelah pembayaran diverifikasi supaya saya mempunyai bukti resmi keikutsertaan.

### US-PST-08

Sebagai **Peserta**, saya ingin melihat progres lomba secara realtime supaya saya mengetahui perkembangan pertandingan.

### US-PST-09

Sebagai **Peserta**, saya ingin melihat ranking akhir supaya saya mengetahui hasil pertandingan.

### US-PST-10

Sebagai **Peserta yang menjadi juara**, saya ingin mengunduh piagam dalam bentuk PDF supaya dapat menyimpan atau mencetak penghargaan.

---

# 5. Pembagian Fitur MVP / V2 / Nanti

| Fitur | MVP | V2 | Nanti |
|---|:---:|:---:|:---:|
| Authentication | ✓ | | |
| Role Owner | ✓ | | |
| Role Admin | ✓ | | |
| Role Juri | ✓ | | |
| Role Peserta | ✓ | | |
| Multi-event | ✓ | | |
| Multi-admin | ✓ | | |
| Multi-kelas | ✓ | | |
| Data burung | ✓ | | |
| Foto burung | ✓ | | |
| Pendaftaran peserta | ✓ | | |
| Upload bukti pembayaran | ✓ | | |
| Verifikasi pembayaran | ✓ | | |
| Bukti pendaftaran PDF | ✓ | | |
| Penempatan juri per kelas | ✓ | | |
| Penilaian 1–10 | ✓ | | |
| Multiple juri | ✓ | | |
| Rata-rata nilai juri | ✓ | | |
| TOPSIS | ✓ | | |
| Ranking per kelas | ✓ | | |
| Warning | ✓ | | |
| Diskualifikasi | ✓ | | |
| Catatan diskualifikasi | ✓ | | |
| Realtime Laravel Reverb | ✓ | | |
| Landing page | ✓ | | |
| Event upcoming | ✓ | | |
| Event berlangsung | ✓ | | |
| Event selesai | ✓ | | |
| Daftar peserta/burung | ✓ | | |
| Hadiah uang/barang | ✓ | | |
| Ranking akhir | ✓ | | |
| Piagam PDF | ✓ | | |
| Audit aktivitas penting | ✓ | | |
| QR verification piagam | | ✓ | |
| Nomor peserta QR | | ✓ | |
| Notification center | | ✓ | |
| Email notification | | ✓ | |
| WhatsApp notification | | | ✓ |
| Payment gateway | | | ✓ |
| Live streaming | | | ✓ |
| Mobile application | | | ✓ |
| Statistik juri | | ✓ | |
| Statistik peserta | | ✓ | |
| Riwayat performa burung | | ✓ | |
| Export Excel | | ✓ | |
| Backup event dari dashboard | | ✓ | |
| Pengaturan bobot TOPSIS fleksibel | | ✓ | |

---

# 6. Functional Requirements MVP

# 6.1 Authentication dan Authorization

Sistem mempunyai empat role:

- Owner
- Admin
- Juri
- Peserta

Pengunjung landing page tidak diwajibkan login.

### Permission Owner

Owner dapat:

- Membuat event.
- Mengedit event.
- Melihat seluruh event miliknya.
- Menambahkan admin.
- Menghapus hubungan admin dengan event.
- Membuat akun admin.
- Menggunakan akun admin yang sudah ada.

### Permission Admin

Admin hanya dapat mengakses event yang ditugaskan kepadanya.

Admin dapat:

- Mengelola profil event.
- Mengelola kelas.
- Mengelola hadiah.
- Mengelola peserta.
- Memverifikasi pembayaran.
- Mengelola juri.
- Menempatkan juri.
- Melihat penilaian.
- Melihat pelanggaran.
- Melihat ranking.
- Memfinalisasi lomba.

Admin tidak boleh mengelola event lain apabila tidak ditugaskan.

### Permission Juri

Juri hanya dapat melihat:

- Event tempat dirinya menjadi juri.
- Kelas yang ditugaskan.
- Peserta kelas tersebut.

Juri tidak boleh menilai kelas yang tidak ditugaskan.

### Permission Peserta

Peserta dapat:

- Mengelola profil.
- Mengelola data burung miliknya.
- Mendaftar event.
- Upload pembayaran.
- Melihat pendaftaran miliknya.
- Mengunduh bukti pendaftaran.
- Melihat hasil.
- Mengunduh piagam miliknya.

---

# 6.2 Manajemen Event

Owner membuat event.

Minimal data:

- Nama event
- Deskripsi
- Banner/poster
- Lokasi
- Alamat lengkap
- Tanggal event
- Waktu mulai
- Waktu selesai
- Tanggal buka pendaftaran
- Tanggal tutup pendaftaran
- Contact person
- Informasi tambahan

### Status event

```text
DRAFT
   ↓
UPCOMING
   ↓
ONGOING
   ↓
COMPLETED
```

Tambahan:

```text
CANCELLED
```

### DRAFT

Tidak ditampilkan pada landing page.

### UPCOMING

Sudah dipublikasikan tetapi belum berlangsung.

### ONGOING

Event sedang berlangsung.

Landing page menampilkan progres penjurian realtime.

### COMPLETED

Event selesai dan hasil resmi dapat dilihat.

### CANCELLED

Event dibatalkan.

---

# 6.3 Multi-Admin Event

Relasi:

```text
Event
 ├── Admin 1
 ├── Admin 2
 └── Admin 3
```

Owner dapat:

1. Mencari user yang sudah mempunyai akun.
2. Memilih user tersebut sebagai admin.
3. Atau membuat akun baru.
4. Menambahkan admin ke event.

Satu admin dapat ditugaskan ke lebih dari satu event.

---

# 6.4 Manajemen Kelas Lomba

Satu event mempunyai `1..n` kelas.

Contoh:

```text
Albab Bird Competition 2026

├── Murai Batu Utama
├── Murai Batu Eksekutif
├── Murai Batu Muda
├── Kacer A
└── Cucak Hijau Utama
```

Data kelas:

- Event
- Nama kelas
- Jenis burung
- Deskripsi
- Harga pendaftaran
- Kuota
- Jadwal mulai
- Jadwal selesai
- Status
- Informasi tambahan

Status kelas:

```text
UPCOMING
ONGOING
JUDGING_COMPLETED
FINALIZED
```

Ranking tidak boleh digabungkan antar kelas.

---

# 6.5 Hadiah

Hadiah disimpan berdasarkan kelas dan peringkat.

Contoh:

| Peringkat | Jenis | Hadiah |
|---|---|---|
| Juara 1 | Barang | Sepeda Motor |
| Juara 2 | Uang | Rp5.000.000 |
| Juara 3 | Uang | Rp3.000.000 |
| Juara 4 | Barang | Sangkar Premium |

Jenis hadiah:

```text
CASH
ITEM
OTHER
```

Untuk hadiah uang:

- Nama hadiah
- Nominal

Untuk hadiah barang:

- Nama barang
- Deskripsi

Jumlah pemenang tidak wajib sama antar kelas.

Sebuah kelas dapat mempunyai:

- Juara 1–3
- Juara 1–4
- atau jumlah lain sesuai konfigurasi hadiah.

---

# 6.6 Data Burung

Peserta dapat menyimpan data burung.

Minimal:

- Nama burung
- Jenis burung
- Foto
- Pemilik
- Keterangan

Data peserta diambil dari profil user:

- Nama
- Nomor telepon
- Alamat

Foto burung dapat digunakan sebagai cover pada daftar peserta event.

---

# 6.7 Pendaftaran Lomba

Flow:

```text
Peserta
   ↓
Pilih Event
   ↓
Pilih Kelas
   ↓
Pilih / Tambah Burung
   ↓
Konfirmasi Data
   ↓
Daftar
   ↓
Upload Bukti Pembayaran
   ↓
Menunggu Verifikasi
```

Satu pendaftaran mewakili:

```text
1 Peserta
+
1 Burung
+
1 Kelas
```

Contoh:

```text
Budi
Burung: Panglima
Kelas: Murai Batu Utama
```

adalah satu entry kompetisi.

---

# 6.8 Status Pendaftaran

```text
PENDING_PAYMENT
      ↓
WAITING_VERIFICATION
      ↓
VERIFIED
```

Kemungkinan lain:

```text
PAYMENT_REJECTED
CANCELLED
DISQUALIFIED
```

### PENDING_PAYMENT

Peserta sudah mendaftar tetapi belum upload bukti.

### WAITING_VERIFICATION

Bukti sudah diunggah.

### VERIFIED

Pembayaran disetujui admin.

Hanya peserta `VERIFIED` yang menjadi peserta resmi.

### PAYMENT_REJECTED

Admin menolak pembayaran dan wajib memberi alasan.

Peserta dapat mengunggah bukti baru.

---

# 6.9 Verifikasi Pembayaran

Admin melihat:

- Nama peserta
- Nama burung
- Kelas
- Biaya
- Bukti pembayaran
- Waktu upload

Action:

```text
VERIFIKASI
TOLAK
```

Jika ditolak, `rejection_reason` wajib diisi.

Tidak ada payment gateway pada MVP.

---

# 6.10 Bukti Pendaftaran

Setelah pembayaran berstatus `VERIFIED`, peserta dapat membuka dan mengunduh bukti pendaftaran PDF.

Minimal isi:

- Nama event
- Nama peserta
- Nama burung
- Jenis burung
- Kelas
- Nomor pendaftaran
- Nomor gantangan jika sudah tersedia
- Tanggal event
- Lokasi
- Status pembayaran: VERIFIED

Peserta yang belum diverifikasi tidak dapat mengunduh bukti resmi.

---

# 6.11 Manajemen Juri

Admin dapat:

```text
Cari akun
      ↓
Pilih sebagai Juri

atau

Buat akun baru
      ↓
Tetapkan sebagai Juri
```

Setelah itu admin memilih kelas.

Contoh:

```text
Murai Batu Utama

Juri:
- Juri A
- Juri B
- Juri C
```

Juri A tidak otomatis dapat menilai kelas lain.

---

# 6.12 Kriteria Penilaian

MVP menggunakan kriteria tetap.

| Code | Kriteria | Bobot | Jenis |
|---|---|---:|---|
| C1 | Irama Lagu | 0,40 | Benefit |
| C2 | Durasi Kerja / Kestabilan | 0,20 | Benefit |
| C3 | Volume | 0,20 | Benefit |
| C4 | Gaya | 0,10 | Benefit |
| C5 | Fisik | 0,10 | Benefit |

Total:

```text
0.40 + 0.20 + 0.20 + 0.10 + 0.10 = 1.00
```

Semua merupakan **benefit criterion**, artinya semakin besar nilai semakin baik.

Range:

```text
MIN = 1
MAX = 10
```

Backend harus menolak:

```text
score < 1
score > 10
```

---

# 6.13 Form Penilaian Juri

Contoh:

**Burung:** Panglima  
**Nomor:** 17

| Kriteria | Nilai |
|---|---:|
| Irama Lagu | 9 |
| Durasi Kerja | 8 |
| Volume | 9 |
| Gaya | 7 |
| Fisik | 8 |

Action:

```text
SIMPAN DRAFT
SUBMIT NILAI
```

Nilai draft belum dimasukkan sebagai nilai final.

Setelah `SUBMIT`, data penilaian mempunyai status:

```text
SUBMITTED
```

---

# 6.14 Independensi Penilaian Juri

Juri tidak boleh melihat nilai juri lain sebelum dirinya melakukan submit.

Tujuan:

- Menghindari pengaruh antarjuri.
- Mengurangi kecenderungan menyamakan nilai.
- Menjaga independensi penilaian.

Admin boleh melihat progres:

```text
Juri A: Submitted
Juri B: Submitted
Juri C: Pending
```

---

# 6.15 Rata-rata Nilai Juri

Misalnya terdapat tiga juri.

Nilai **Irama Lagu**:

```text
Juri A = 8
Juri B = 9
Juri C = 10
```

Maka:

```text
(8 + 9 + 10) / 3 = 9
```

Perhitungan dilakukan pada setiap kriteria.

Hasil akhir sebelum TOPSIS:

| Burung | Irama | Durasi | Volume | Gaya | Fisik |
|---|---:|---:|---:|---:|---:|
| Panglima | 9.00 | 8.33 | 9.00 | 8.00 | 8.33 |
| Samurai | 8.33 | 9.00 | 8.67 | 9.00 | 8.00 |
| Raja | 8.67 | 8.00 | 8.00 | 8.67 | 9.00 |

Matriks tersebut menjadi input TOPSIS.

---

# 6.16 Perhitungan TOPSIS

TOPSIS dilakukan **per kelas**.

Peserta yang `DISQUALIFIED` dikeluarkan dari matriks TOPSIS.

---

## Step 1 — Decision Matrix

Misalkan:

```text
Xij
```

adalah rata-rata nilai peserta `i` untuk kriteria `j`.

---

## Step 2 — Normalisasi

Formula:

```text
rij = xij / √Σ(xij²)
```

---

## Step 3 — Normalisasi Berbobot

```text
yij = wj × rij
```

Bobot:

```text
C1 = 0.40
C2 = 0.20
C3 = 0.20
C4 = 0.10
C5 = 0.10
```

---

## Step 4 — Solusi Ideal Positif

Karena semua kriteria merupakan benefit:

```text
A+ = nilai maksimum setiap kriteria
```

---

## Step 5 — Solusi Ideal Negatif

```text
A- = nilai minimum setiap kriteria
```

---

## Step 6 — Jarak Solusi Ideal Positif

```text
Di+ = √Σ(yij - Aj+)²
```

---

## Step 7 — Jarak Solusi Ideal Negatif

```text
Di- = √Σ(yij - Aj-)²
```

---

## Step 8 — Preference Value

```text
Vi = Di- / (Di+ + Di-)
```

Nilai `Vi` semakin tinggi berarti alternatif semakin baik.

---

## Step 9 — Ranking

Ranking:

```text
ORDER BY preference_value DESC
```

Contoh:

| Ranking | Burung | TOPSIS |
|---:|---|---:|
| 1 | Panglima | 0.8234 |
| 2 | Samurai | 0.7511 |
| 3 | Raja | 0.6878 |

Nilai TOPSIS disimpan minimal empat angka desimal untuk kebutuhan audit dan transparansi.

---

# 6.17 Waktu Perhitungan TOPSIS

TOPSIS provisional dapat diperbarui ketika nilai baru masuk.

Namun hasil berstatus:

```text
PROVISIONAL
```

selama kelas masih berlangsung.

Setelah penjurian selesai, Admin melakukan:

```text
FINALIZE RESULT
```

Setelah finalisasi:

```text
FINAL
```

Hasil resmi tidak boleh berubah tanpa mekanisme pembatalan finalisasi yang memiliki audit trail.

---

# 6.18 Sistem Pelanggaran

Juri mempunyai tombol:

```text
BERI PERINGATAN
DISKUALIFIKASI
```

---

## Pelanggaran dengan Warning

Contoh:

- Berteriak.
- Mengganggu peserta lain.
- Memprovokasi peserta lain.

Pelanggaran pertama:

```text
WARNING 1
```

Apabila kembali melakukan pelanggaran yang masuk kategori peringatan:

```text
DISQUALIFIED
```

---

## Pelanggaran Langsung Diskualifikasi

Contoh:

### Terlambat menggantangkan burung

```text
DIRECT DISQUALIFICATION
```

### Memindah atau memanipulasi nomor gantangan

```text
DIRECT DISQUALIFICATION
```

### Pelanggaran berat lain

Juri dapat memilih:

```text
DISQUALIFICATION
```

dengan `note` wajib.

---

# 6.19 Data Pelanggaran

Setiap pelanggaran menyimpan:

- Peserta
- Burung
- Entry lomba
- Kelas
- Juri
- Tipe tindakan
- Alasan
- Catatan
- Tanggal
- Jam

Contoh:

```text
Peserta: Budi
Burung: Panglima
Nomor: 17

Action:
DISQUALIFIED

Reason:
Manipulasi nomor gantangan

Note:
Nomor gantangan dipindahkan dari posisi yang telah ditentukan panitia.

Judge:
Juri A
```

---

# 6.20 Pengaruh Diskualifikasi

Entry yang sudah `DISQUALIFIED`:

- Tidak dapat dinilai lagi.
- Tidak masuk rata-rata akhir.
- Tidak masuk perhitungan TOPSIS.
- Tidak mempunyai ranking.
- Tidak dapat menjadi juara.
- Status diskualifikasi ditampilkan di halaman hasil.

Nilai yang sebelumnya sudah diberikan tetap disimpan sebagai audit, tetapi tidak digunakan dalam perhitungan akhir.

---

# 6.21 Realtime dengan Laravel Reverb

Teknologi:

```text
Laravel
Laravel Reverb
Laravel Echo
WebSocket
```

Event penting yang dibroadcast:

```text
JudgingStarted
ScoreSubmitted
JudgingProgressUpdated
ParticipantWarned
ParticipantDisqualified
ClassJudgingCompleted
RankingUpdated
ClassResultFinalized
```

Contoh channel:

```text
event.{eventId}

class.{classId}
```

Dashboard admin dan landing page event menerima perubahan tanpa reload manual.

---

# 6.22 Progress Penjurian

Sistem menghitung:

```text
Jumlah penilaian selesai
------------------------- × 100%
Total penilaian wajib
```

Misalnya:

```text
20 peserta
3 juri

Total evaluasi = 60

Submitted = 45

Progress = 75%
```

Landing page dapat menampilkan:

```text
Murai Batu Utama

Penjurian:
███████████████░░░░░ 75%

45 / 60 Penilaian selesai
```

---

# 6.23 Landing Page Utama

Halaman publik:

```text
/
```

Landing page adalah website resmi brand/penyelenggara GantangScore. Fokus utamanya adalah agenda lomba, lokasi, kelas, pendaftaran, hasil, dan profil penyelenggara—bukan memperkenalkan aplikasi atau fitur software. Kapabilitas sistem hanya muncul secara kontekstual untuk membangun kepercayaan terhadap penyelenggaraan event.

Bagian utama:

```text
Navbar
Hero
Event Sedang Berlangsung
Event Akan Datang
Event Selesai
Tentang Penyelenggara
Footer
```

---

# 6.24 Upcoming Event

Card event menampilkan:

- Poster
- Nama event
- Tanggal
- Lokasi
- Status
- Mulai harga pendaftaran

Ketika dibuka:

- Informasi event
- Lokasi
- Jadwal
- Daftar kelas
- Harga
- Hadiah
- Jumlah peserta
- Tombol daftar

---

# 6.25 Ongoing Event

Menampilkan:

- Nama event
- Kelas aktif
- Peserta
- Foto burung
- Nama burung
- Nama pemilik
- Nomor gantangan
- Progres penjurian
- Status

Contoh:

```text
No. 17
Panglima
Pemilik: Budi

Penjurian 3/3 Juri
SELESAI
```

Perubahan nilai/progres diperbarui melalui Reverb.

Data yang belum final harus diberi label:

```text
HASIL SEMENTARA
```

---

# 6.26 Completed Event

Menampilkan event yang sudah selesai.

Ketika dibuka:

```text
Albab Cup 2026
```

User dapat memilih kelas.

Contoh:

```text
Murai Batu Utama
```

Hasil:

```text
🥇 Juara 1
Panglima
Pemilik: Budi

🥈 Juara 2
Samurai
Pemilik: Ahmad

🥉 Juara 3
Raja
Pemilik: Joko
```

Informasi hadiah juga ditampilkan.

---

# 6.27 Dashboard Peserta

Menu:

```text
Dashboard
Profil
Burung Saya
Pendaftaran Saya
Piagam Saya
```

Dashboard menampilkan:

- Upcoming competition
- Pendaftaran pending
- Pembayaran menunggu verifikasi
- Pendaftaran terverifikasi
- Event yang sedang diikuti
- Riwayat hasil

---

# 6.28 Piagam Penghargaan

Piagam hanya dibuat untuk pemenang resmi setelah hasil `FINAL`.

Isi minimal:

- Nama event
- Nama peserta/pemilik
- Nama burung
- Jenis burung
- Nama kelas
- Peringkat
- Tanggal
- Penyelenggara

Contoh:

```text
PIAGAM PENGHARGAAN

Diberikan kepada

BUDI

bersama burung
PANGLIMA

sebagai

JUARA 1

Kelas Murai Batu Utama

Albab Bird Competition 2026
```

Peserta dapat:

```text
LIHAT PDF
DOWNLOAD PDF
PRINT
```

---

# 6.29 Finalisasi Hasil

Admin dapat melakukan finalisasi hanya apabila:

- Event/kelas sedang berlangsung.
- Peserta tersedia.
- Penilaian telah memenuhi syarat.
- Tidak ada proses penjurian yang masih dibutuhkan.
- Minimal 3 Juri aktif; seluruh penugasan aktif menyelesaikan nilai setiap peserta eligible.
- Seluruh draw sudah diselesaikan lewat lempar dadu nyata dan dicatat Admin sesuai keputusan Tahap 7.

Saat finalisasi:

```text
Average Scores
      ↓
TOPSIS
      ↓
Ranking
      ↓
Store Final Results
      ↓
Assign Winners
      ↓
Generate Certificates
      ↓
Publish
```

Hasil final harus disimpan sebagai snapshot agar perubahan data di masa depan tidak mengubah hasil historis.

---

# 6.30 Audit Trail

Aktivitas penting harus dicatat.

Minimal:

```text
EVENT_CREATED
EVENT_UPDATED

ADMIN_ASSIGNED

REGISTRATION_CREATED

PAYMENT_UPLOADED
PAYMENT_VERIFIED
PAYMENT_REJECTED

SCORE_SUBMITTED

WARNING_CREATED
PARTICIPANT_DISQUALIFIED

RESULT_FINALIZED
```

Data:

- User
- Action
- Object
- Object ID
- Timestamp
- Additional data

---

# 7. Sketsa Data Model

## users

```text
id
name
email
phone
password
status
created_at
updated_at
```

---

## user_roles

```text
id
user_id
role

OWNER
ADMIN
JUDGE
PARTICIPANT
```

Satu user dapat memiliki lebih dari satu role apabila nantinya dibutuhkan.

---

## participant_profiles

```text
id
user_id
address
city
province
postal_code
created_at
updated_at
```

---

## events

```text
id
owner_id
name
slug
description
banner
location_name
address
registration_open_at
registration_close_at
start_at
end_at
contact_person
contact_phone
status
published_at
created_at
updated_at
```

---

## event_admins

```text
id
event_id
user_id
created_at
```

Unique:

```text
event_id + user_id
```

---

## competition_classes

```text
id
event_id
name
bird_type
description
registration_fee
quota
start_at
end_at
status
created_at
updated_at
```

---

## prizes

```text
id
competition_class_id
rank
type
title
description
cash_amount
created_at
updated_at
```

---

## birds

```text
id
owner_id
name
bird_type
photo
description
created_at
updated_at
```

---

## registrations

```text
id
competition_class_id
participant_id
bird_id
registration_number
gantangan_number
status
registered_at
verified_at
created_at
updated_at
```

---

## payments

```text
id
registration_id
amount
proof_file
status
uploaded_at
verified_by
verified_at
rejection_reason
created_at
updated_at
```

Status:

```text
WAITING_VERIFICATION
VERIFIED
REJECTED
```

---

## judge_assignments

```text
id
competition_class_id
judge_id
created_at
```

Unique:

```text
competition_class_id + judge_id
```

---

## criteria

Untuk MVP dapat disimpan sebagai master data:

```text
id
code
name
weight
type
min_score
max_score
```

Isi:

```text
C1 Irama Lagu          0.40 BENEFIT 1 10
C2 Durasi Kerja        0.20 BENEFIT 1 10
C3 Volume              0.20 BENEFIT 1 10
C4 Gaya                0.10 BENEFIT 1 10
C5 Fisik               0.10 BENEFIT 1 10
```

---

## judge_evaluations

Mewakili satu proses penilaian seorang juri terhadap satu peserta.

```text
id
registration_id
judge_id
status
submitted_at
created_at
updated_at
```

Status:

```text
DRAFT
SUBMITTED
```

Unique:

```text
registration_id + judge_id
```

---

## judge_scores

```text
id
judge_evaluation_id
criterion_id
score
created_at
updated_at
```

---

## violations

```text
id
registration_id
judge_id
action
reason_code
reason
note
created_at
```

Action:

```text
WARNING
DISQUALIFICATION
```

---

## topsis_results

```text
id
competition_class_id
registration_id
irama_average
durasi_average
volume_average
gaya_average
fisik_average
positive_distance
negative_distance
preference_value
rank
status
calculated_at
finalized_at
```

Status:

```text
PROVISIONAL
FINAL
```

---

## certificates

```text
id
event_id
competition_class_id
registration_id
rank
file_path
generated_at
```

---

## activity_logs

```text
id
user_id
action
subject_type
subject_id
metadata
created_at
```

---

# Relasi Utama

```text
User
 │
 ├── owns ───────────── Event
 │
 ├── event_admins ───── Event
 │
 ├── judge_assignments ─ Competition Class
 │
 ├── registrations
 │
 └── birds


Event
 │
 ├── Event Admins
 │
 └── Competition Classes
          │
          ├── Prizes
          ├── Judge Assignments
          └── Registrations
                  │
                  ├── Bird
                  ├── Payment
                  ├── Judge Evaluations
                  │        └── Scores
                  │
                  ├── Violations
                  ├── TOPSIS Result
                  └── Certificate
```

---

# 8. Edge Cases dan Failure States

## EC-01 — Pembayaran belum diverifikasi

Peserta tidak boleh dianggap peserta resmi.

Sistem tidak memasukkannya ke daftar penilaian juri.

---

## EC-02 — Pembayaran ditolak

Peserta dapat upload ulang bukti pembayaran selama pendaftaran masih diperbolehkan.

Riwayat bukti pembayaran lama tidak boleh hilang dari audit.

---

## EC-03 — Juri mencoba menilai kelas lain

Backend mengembalikan:

```text
403 Forbidden
```

Validasi tidak hanya dilakukan di UI.

---

## EC-04 — Juri mengirim nilai 11

Request ditolak.

```text
Score must be between 1 and 10.
```

---

## EC-05 — Juri belum menilai semua kriteria

`SUBMIT` ditolak.

Semua lima kriteria wajib memiliki nilai.

---

## EC-06 — Double submit

Sistem mencegah pencatatan evaluasi ganda untuk kombinasi:

```text
registration_id + judge_id
```

---

## EC-07 — Peserta didiskualifikasi setelah mendapatkan nilai

Nilai tetap tersimpan untuk audit.

Peserta dikeluarkan dari TOPSIS.

---

## EC-08 — Warning kedua

Jika peserta sudah pernah mendapatkan warning yang berlaku dan melakukan pelanggaran warning kembali:

```text
DISQUALIFIED
```

Sistem meminta juri mengisi catatan kejadian.

---

## EC-09 — Direct Disqualification

Pelanggaran seperti:

- Terlambat menggantangkan.
- Manipulasi nomor.

tidak membutuhkan warning terlebih dahulu.

---

## EC-10 — Koneksi WebSocket gagal

Sistem tidak boleh bergantung sepenuhnya pada WebSocket untuk menyimpan data.

Laravel HTTP request/API tetap menjadi source of truth.

Frontend dapat melakukan fallback refresh/polling ringan apabila koneksi Reverb terputus.

---

## EC-11 — Dua admin memverifikasi pembayaran bersamaan

Database harus mencegah status menjadi inkonsisten.

Gunakan transaction dan validasi current state.

---

## EC-12 — Nilai berubah ketika TOPSIS provisional sudah dihitung

Ranking provisional dihitung ulang.

Ranking final tidak boleh berubah apabila sudah difinalisasi.

---

## EC-13 — Hanya satu peserta valid

TOPSIS normal tidak menghasilkan perbandingan yang bermakna karena ideal positif dan negatif sama.

Jika hanya terdapat satu peserta eligible:

```text
Rank = 1
```

dan sistem tidak perlu menggunakan coefficient TOPSIS untuk membandingkannya dengan peserta lain.

---

## EC-14 — Tidak ada peserta eligible

Finalisasi tidak boleh dilakukan.

---

## EC-15 — Semua peserta didiskualifikasi

Tidak ada pemenang.

Sistem menampilkan:

```text
Tidak ada peserta yang memenuhi syarat untuk mendapatkan peringkat.
```

---

## EC-16 — Nilai TOPSIS sama

Sistem menandai:

```text
TIE
```

Keputusan Owner, 21 Oktober 2026: peserta draw melakukan lempar dadu nyata di lokasi lomba; total tertinggi menang. Admin mencatat urutan hasil undian pada kelompok peserta yang seri, disertai catatan dan konfirmasi. Sistem tidak melempar dadu dan tidak memilih pemenang secara acak. Jika hasil lemparan masih seri, hasil draw belum dapat dicatat sebagai urutan final. Nilai TOPSIS asli dan riwayat undian tetap disimpan.

---

## EC-17 — Juri kehilangan koneksi

Nilai yang sudah disimpan sebagai `DRAFT` tetap tersedia ketika login kembali.

---

## EC-18 — Event selesai tetapi kelas belum final

Event tidak boleh dianggap benar-benar selesai untuk publikasi hasil sampai seluruh kelas yang diwajibkan sudah diselesaikan atau ditutup admin.

---

## EC-19 — Upload bukan gambar valid

Foto burung dan bukti pembayaran harus melalui validasi MIME/type dan ukuran file.

---

## EC-20 — Cross-event access

Admin Event A tidak boleh mengakses URL resource Event B hanya dengan mengganti ID pada URL.

Semua akses wajib menggunakan policy/authorization Laravel pada backend.

---

# 9. Success Metrics

## Product Metrics

### SM-01

≥95% pendaftaran peserta dapat dilakukan tanpa bantuan admin.

### SM-02

100% peserta yang masuk penjurian harus memiliki pembayaran `VERIFIED`.

### SM-03

100% ranking final dapat direproduksi dari data score yang tersimpan.

### SM-04

Tidak ada peserta `DISQUALIFIED` yang masuk hasil TOPSIS final.

### SM-05

Tidak ada juri yang dapat memberikan nilai pada kelas di luar assignment-nya.

---

## Operational Metrics

### SM-06

Update realtime dari submission nilai sampai dashboard menerima perubahan:

```text
Target < 2 detik
```

dalam kondisi koneksi normal.

### SM-07

Waktu perhitungan ranking TOPSIS untuk satu kelas:

```text
Target < 1 detik
```

untuk skala lomba normal.

### SM-08

Tidak ada duplicate score untuk kombinasi:

```text
Judge + Registration
```

---

## Accuracy Metrics

### SM-09

Perhitungan:

```text
Average
Normalization
Weighted Normalization
Ideal Solution
Distance
Preference
Ranking
```

harus memiliki automated test.

Target:

```text
100% test pass
```

sebelum release.

---

## Business Metrics

### SM-10

Admin dapat melakukan alur berikut tanpa spreadsheet tambahan:

```text
Create Class
→ Register Participant
→ Verify Payment
→ Judge
→ TOPSIS
→ Winner
→ Certificate
```

---

# 10. Open Questions

Requirement berikut belum ditentukan dan harus diputuskan sebelum implementasi terkait dimulai.

## OQ-01 — Multiple Class

Apakah **burung yang sama** diperbolehkan mengikuti lebih dari satu kelas dalam event yang sama?

Contoh:

```text
Panglima
→ Murai Batu Utama
→ Murai Batu Eksekutif
```

Belum ditentukan.

---

## OQ-02 — Tie Break

Jika dua peserta mendapatkan nilai TOPSIS yang sama, bagaimana menentukan posisi?

Kemungkinan aturan yang perlu dipilih:

- Irama Lagu tertinggi.
- Total rata-rata tertinggi.
- Penilaian ulang.
- Keputusan juri.
- Peringkat sama.

Diputuskan pada Tahap 7: lempar dadu nyata; Admin mencatat urutan resmi kelompok seri dengan audit, tanpa mengubah nilai TOPSIS. Semua draw harus diselesaikan sebelum finalisasi. Lihat `TOPSIS.md`.

---

## OQ-03 — Jumlah Juri Minimum

Apakah setiap kelas wajib memiliki jumlah juri tertentu?

Contoh:

```text
Minimal 3 juri
```

atau jumlah juri bebas.

Diputuskan pada Tahap 7: minimal 3 Juri aktif untuk finalisasi kelas. Seluruh Juri yang diwajibkan harus menyelesaikan evaluasi lengkap setiap peserta eligible. Ranking provisional boleh memakai evaluasi submitted yang tersedia, tetapi tidak resmi dan tidak dipublikasikan.

---

## OQ-04 — Juri Tidak Hadir

Jika sebuah kelas mempunyai tiga juri tetapi hanya dua yang dapat melakukan penilaian, apakah hasil:

- Tetap dihitung dari dua juri?
- Menunggu juri pengganti?
- Tidak dapat difinalisasi?

Diputuskan pada Tahap 7: tersedia penggantian Juri absen oleh Admin event terkait. Penugasan lama/draft tetap tersimpan; akses penilaian lama dicabut, dan pengganti menilai sendiri seluruh peserta. Penggantian yang akan mengesampingkan nilai submitted tidak diizinkan tanpa keputusan khusus mengenai penggantian setelah penilaian parsial. Jumlah Juri tidak dikurangi diam-diam.

---

## OQ-05 — Koreksi Nilai Setelah Submit

Apakah juri diperbolehkan mengubah nilai setelah menekan `SUBMIT`?

Jika diperbolehkan, perlu ditentukan:

- Sampai kapan?
- Perlu persetujuan admin atau tidak?
- Apakah nilai lama disimpan?

Belum ditentukan.

---

## OQ-06 — Pembatalan Diskualifikasi

Apakah Admin atau Owner dapat membatalkan keputusan diskualifikasi dari juri?

Belum ditentukan.

---

## OQ-07 — Live Score Publik

Saat event berlangsung, perlu dipastikan apakah publik dapat melihat:

**A.** Hanya progres penjurian.

atau

**B.** Progres + rata-rata nilai sementara.

atau

**C.** Progres + ranking TOPSIS sementara.

Requirement saat ini memastikan progres realtime tersedia, tetapi tingkat detail nilai yang boleh ditampilkan ke publik perlu ditetapkan.

---

## OQ-08 — Nomor Gantangan

Diputuskan pengguna pada 29 Oktober 2026: peserta memilih nomor gantangan sendiri dan langsung memperoleh nomor tersebut tanpa verifikasi Admin. Nomor wajib diisi saat pendaftaran baru, unik per kelas, dan ditolak dengan pesan “Nomor gantangan sudah digunakan pada kelas ini. Silakan pilih nomor lain.” jika bertabrakan. Verifikasi pembayaran tetap berjalan terpisah.

Nomor memakai angka positif; awalan nol dinormalisasi (017 = 17). Rentang fisik gantangan belum ditetapkan, sehingga batas kuota peserta tidak dianggap otomatis sebagai batas nomor. Pendaftaran lama yang nomornya kosong dapat memilih sekali sebelum penjurian dimulai; nomor yang sudah terisi tidak dapat diganti melalui alur normal. Tidak ada pelepasan nomor otomatis ketika pembayaran ditolak atau status berubah; riwayat tetap dipertahankan.

---

## OQ-09 — Kuota

Jika kelas sudah penuh:

- Pendaftaran langsung ditutup?
- Dibuat waiting list?

Belum ditentukan.

---

## OQ-10 — Perubahan Harga

Jika admin mengubah harga kelas setelah peserta sudah mendaftar, apakah pendaftar lama menggunakan:

- Harga saat mendaftar.
- Harga terbaru.

Rekomendasi implementasi membutuhkan penyimpanan `registration_fee_snapshot`, tetapi aturan bisnis final perlu dikonfirmasi.

---

## OQ-11 — Piagam

Perlu ditentukan format final piagam:

- Logo penyelenggara.
- Logo sponsor.
- Tanda tangan ketua panitia.
- Tanda tangan juri.
- Stempel.
- Nomor piagam.

---

## OQ-12 — Hadiah

Perlu dipastikan apakah hadiah selalu ditentukan **per kelas**, atau event juga dapat mempunyai hadiah keseluruhan seperti:

```text
Best of Event
Best Performance
Grand Champion
```

Konsep saat ini menggunakan hadiah per kelas.

---

# Definition of Done MVP

Produk dianggap selesai untuk versi MVP jika flow berikut dapat dilakukan secara utuh:

```text
OWNER
  ↓
Login
  ↓
Create Event
  ↓
Assign Admin
  ↓
ADMIN
  ↓
Create Classes
  ↓
Set Registration Fee
  ↓
Set Prizes
  ↓
Create / Assign Judges
  ↓
PARTICIPANT
  ↓
Register Account
  ↓
Create Bird
  ↓
Choose Event
  ↓
Choose Class
  ↓
Register Bird
  ↓
Upload Payment
  ↓
ADMIN
  ↓
Verify Payment
  ↓
PARTICIPANT
  ↓
Download Registration Proof
  ↓
EVENT STARTS
  ↓
JUDGE
  ↓
Score Participant
  ↓
Warning / DQ if needed
  ↓
Submit Scores
  ↓
SYSTEM
  ↓
Average Judge Scores
  ↓
Calculate TOPSIS
  ↓
Update Realtime Progress
  ↓
ADMIN
  ↓
Review Ranking
  ↓
Finalize Result
  ↓
SYSTEM
  ↓
Publish Winners
  ↓
Generate Certificates
  ↓
PUBLIC
  ↓
View Final Result
  ↓
WINNER
  ↓
Download Certificate PDF
```

MVP belum dianggap selesai apabila salah satu proses inti berikut masih membutuhkan perhitungan manual di luar sistem:

```text
Penilaian
Rata-rata nilai
TOPSIS
Ranking
Diskualifikasi
Penentuan pemenang
Pembuatan bukti pendaftaran
Pembuatan piagam pemenang
```

---

# Arsitektur Fungsional Ringkas

```text
                         ┌─────────────────────┐
                         │        OWNER        │
                         │ Event + Event Admin │
                         └──────────┬──────────┘
                                    │
                                    ▼
┌──────────────┐         ┌─────────────────────┐
│   PESERTA    │────────▶│        EVENT        │◀────────┐
│ Registration │         │ Classes / Prizes    │         │
└──────┬───────┘         └──────────┬──────────┘         │
       │                            │                     │
       ▼                            ▼                     │
┌──────────────┐         ┌─────────────────────┐    ┌─────┴─────┐
│   PAYMENT    │────────▶│        ADMIN        │    │   JURI    │
│ Proof Upload │ Verify  │ Event Management    │    │  Scoring  │
└──────────────┘         └─────────────────────┘    └─────┬─────┘
                                                         │
                                                         ▼
                                                ┌─────────────────┐
                                                │ Judge Scores    │
                                                │ 1–10 / Criteria │
                                                └────────┬────────┘
                                                         │
                                                         ▼
                                                ┌─────────────────┐
                                                │ Average Scores  │
                                                └────────┬────────┘
                                                         │
                                                         ▼
                                                ┌─────────────────┐
                                                │     TOPSIS      │
                                                └────────┬────────┘
                                                         │
                              ┌──────────────────────────┴───────────┐
                              ▼                                      ▼
                     ┌─────────────────┐                    ┌────────────────┐
                     │ Laravel Reverb  │                    │ Final Ranking  │
                     │ Realtime Update │                    │ + Winners      │
                     └────────┬────────┘                    └───────┬────────┘
                              │                                     │
                              ▼                                     ▼
                     ┌─────────────────┐                    ┌────────────────┐
                     │ Public Landing  │                    │ Certificate PDF│
                     └─────────────────┘                    └────────────────┘
```

# Prinsip Utama Produk

**Source of truth penilaian**

```text
Judge Score
→ Average
→ TOPSIS
→ Final Ranking
```

Tidak boleh ada perubahan ranking manual tanpa jejak audit.

**Source of truth peserta resmi**

```text
Payment VERIFIED
```

**Source of truth pemenang**

```text
FINAL TOPSIS RESULT
```

**Scope ranking**

```text
1 Event
   ↓
N Classes
   ↓
TOPSIS dihitung masing-masing per Class
```

Dengan demikian peserta Murai Batu Utama tidak dibandingkan dengan peserta Kacer, Cucak Hijau, atau kelas lainnya.

# Implementasi Tahap 8 — Realtime dan Piagam (29 Oktober 2026)

Progres kelas diperbarui langsung untuk publik, Owner/Admin event, dan Juri yang ditugaskan. Publik tetap tidak melihat nilai/ranking provisional. Indikator Connected, Reconnecting, dan Offline fallback menjelaskan koneksi; fallback memeriksa progres setiap 20 detik. Formulir HTTP tetap dapat digunakan ketika realtime terputus.

Piagam hanya untuk posisi hadiah pada snapshot FINAL, mengikuti peringkat resmi setelah penyelesaian draw. Tidak semua peserta/rank otomatis memperoleh piagam dan tidak ada asumsi selalu tiga pemenang. Peserta membuka menu Piagam untuk melihat/cetak/unduh miliknya; status menunggu ditampilkan selama queue belum selesai. Admin event dapat mengantrekan pembuatan/pemulihan dari hasil FINAL, tanpa mengubah hasil resmi.

Penerbitan berulang tidak menggandakan record atau menimpa dokumen yang sudah tersedia. File privat dan akses terotorisasi. Piagam v1 tanpa tanda tangan/stempel; format/aset resmi tetap pertanyaan terbuka. FINAL lama belum mempunyai tanggal/penyelenggara snapshot: panitia memeriksa tanggal event sebelum penerbitan pertama, lalu metadata dibekukan di piagam. Rincian kontrak dan review: `Stage-8-Realtime-Certificate-Contract.md`.
