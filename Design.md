# Design.md — GantangScore

## Sistem Penjurian Kompetisi Gantangan Burung Menggunakan Metode TOPSIS

**Dokumen:** UI/UX Design Specification  
**Produk:** GantangScore  
**Platform:** Web Application  
**Frontend:** Laravel Blade  
**Backend:** Laravel  
**Database:** MySQL  
**Realtime:** Laravel Reverb + Laravel Echo  
**Design Direction:** Clean, professional, warm, trustworthy, competition-focused  
**Reference Pattern:** UI/UX Pro Max — Veterinary Clinic demo  
**Primary Users:** Owner, Admin, Juri, Peserta, Pengunjung Publik

---

# 1. Tujuan Desain

GantangScore harus terasa seperti platform kompetisi profesional, bukan sekadar panel CRUD.

Desain harus mampu menyampaikan tiga hal utama:

1. **Kepercayaan**
   - Penilaian jelas.
   - Ranking transparan.
   - Status kompetisi mudah dipahami.
   - Data peserta dan hasil tidak membingungkan.

2. **Kecepatan Operasional**
   - Admin dapat mengelola event dengan sedikit klik.
   - Juri dapat menilai burung secepat mungkin.
   - Peserta dapat mendaftar dan mengecek status tanpa bertanya ke panitia.

3. **Kejelasan Realtime**
   - Event yang sedang berlangsung harus terasa hidup.
   - Progres penjurian harus terlihat jelas.
   - Update WebSocket tidak mengganggu fokus pengguna.

---

# 2. Prinsip UX Utama

## 2.1 Simple First

Halaman tidak boleh dipenuhi terlalu banyak informasi sekaligus.

Informasi disusun berdasarkan prioritas:

**Primary Action → Status → Data Utama → Detail → Secondary Action**

Contoh untuk peserta:

```text
Status Pendaftaran
↓
Event & Kelas
↓
Burung
↓
Pembayaran
↓
Bukti Pendaftaran
```

---

## 2.2 Status Must Be Obvious

Setiap status penting harus memiliki:

- Label teks.
- Badge.
- Warna.
- Ikon.
- Tooltip jika diperlukan.

Jangan mengandalkan warna saja.

Contoh:

```text
● Terverifikasi
✓ Pembayaran diterima
```

bukan hanya badge hijau tanpa teks.

---

## 2.3 Mobile Friendly

Mayoritas peserta dan sebagian juri kemungkinan menggunakan smartphone.

Karena itu:

- Form pendaftaran harus nyaman di mobile.
- Form penilaian juri harus dapat digunakan satu tangan.
- Tabel kompleks harus berubah menjadi card pada layar kecil.
- Tombol utama minimal tinggi 44 px.
- Input nilai tidak boleh terlalu kecil.

---

## 2.4 Realtime Without Distraction

Laravel Reverb digunakan untuk:

- Update progres penilaian.
- Update status juri.
- Update diskualifikasi.
- Update provisional ranking.
- Update final result.

Perubahan realtime menggunakan animasi halus maksimal 150–250 ms.

Hindari:

- Flash layar.
- Modal otomatis.
- Toast berulang setiap nilai masuk.
- Animasi berlebihan.

---

## 2.5 Public vs Operational UI

Landing page:

**Emosional + informatif + kompetitif.**

Dashboard:

**Fungsional + padat + cepat.**

Landing tidak boleh terlihat seperti admin panel.

Dashboard tidak perlu memiliki hero besar seperti landing page.

---

# 3. Design Personality

GantangScore memiliki karakter:

- Professional
- Competitive
- Trustworthy
- Clean
- Natural
- Premium tetapi tidak mewah berlebihan
- Modern
- Sport-event oriented

Hindari:

- Warna neon.
- Gradient ungu/pink ala aplikasi AI.
- Glassmorphism berlebihan.
- UI terlalu playful.
- Emoji sebagai ikon utama.
- Shadow berat.
- Background terlalu ramai.
- Terlalu banyak card dalam satu viewport.

---

# 4. Design System

## 4.1 Color Palette

Tema mengambil nuansa alam, burung, arena kompetisi, serta kepercayaan.

### Primary

```css
--primary-950: #102A23;
--primary-900: #173A31;
--primary-800: #1E4D40;
--primary-700: #276453;
--primary-600: #327A65;
--primary-500: #3F9278;
--primary-400: #68AD96;
--primary-300: #93C8B5;
--primary-200: #C2E0D4;
--primary-100: #E2F1EB;
--primary-50:  #F2F8F5;
```

Primary utama:

```css
#276453
```

Digunakan untuk:

- CTA utama.
- Active navigation.
- Link utama.
- Progress.
- Primary icon.
- Header dashboard.

---

## 4.2 Accent / Gold

Digunakan untuk:

- Juara.
- Highlight hadiah.
- Featured event.
- TOPSIS ranking.

```css
--gold-700: #A66D14;
--gold-600: #C4861E;
--gold-500: #D9A441;
--gold-300: #EBCB86;
--gold-100: #FBF3DF;
```

---

## 4.3 Neutral

```css
--gray-950: #111827;
--gray-900: #1F2937;
--gray-800: #374151;
--gray-700: #4B5563;
--gray-600: #6B7280;
--gray-500: #9CA3AF;
--gray-400: #D1D5DB;
--gray-300: #E5E7EB;
--gray-200: #EEF0F2;
--gray-100: #F5F6F7;
--gray-50:  #FAFAF9;
--white:    #FFFFFF;
```

---

## 4.4 Semantic Colors

### Success

```css
#15803D
background: #DCFCE7
```

### Warning

```css
#B45309
background: #FEF3C7
```

### Danger

```css
#B42318
background: #FEE4E2
```

### Info

```css
#2563EB
background: #DBEAFE
```

### Draft / Neutral

```css
#667085
background: #F2F4F7
```

---

# 5. Typography

## Primary Font

**Inter**

Fallback:

```css
font-family:
    Inter,
    ui-sans-serif,
    system-ui,
    -apple-system,
    BlinkMacSystemFont,
    "Segoe UI",
    sans-serif;
```

Alasan:

- Mudah dibaca.
- Baik untuk tabel.
- Baik untuk mobile.
- Banyak weight.
- Cocok untuk dashboard.

---

## 5.1 Type Scale

### Landing

```text
Hero H1        56px / 64px / 700
Section H2     40px / 48px / 700
Card H3        22px / 30px / 600
Body Large     18px / 30px / 400
Body           16px / 26px / 400
Small          14px / 20px / 400
Caption        12px / 18px / 500
```

Mobile Hero:

```text
36px / 44px
```

---

### Dashboard

```text
Page Title      28px / 36px / 700
Section Title   20px / 28px / 600
Card Value      28px / 34px / 700
Body            14–16px
Table           14px
Caption          12px
```

---

# 6. Spacing

Gunakan sistem 4px.

```text
4
8
12
16
20
24
32
40
48
64
80
96
```

Container desktop:

```text
max-width: 1280px
padding: 24px
```

Landing:

```text
section vertical:
80–112px desktop
56–72px tablet
40–56px mobile
```

Dashboard:

```text
gap umum: 24px
card padding: 20–24px
```

---

# 7. Radius dan Shadow

## Radius

```text
Button:        10px
Input:         10px
Small Card:    12px
Main Card:     16px
Hero Media:    24px
Modal:         18px
Avatar:        9999px
```

---

## Shadow

Gunakan shadow lembut.

```css
box-shadow:
0 1px 2px rgba(16, 24, 40, 0.04),
0 4px 12px rgba(16, 24, 40, 0.06);
```

Hover:

```css
box-shadow:
0 8px 24px rgba(16, 24, 40, 0.09);
```

---

# 8. Iconography

Gunakan:

**Lucide Icons**

Contoh:

- CalendarDays
- MapPin
- Trophy
- Bird
- Users
- UserRoundCheck
- ClipboardCheck
- Medal
- CircleDollarSign
- ShieldCheck
- Clock3
- Activity
- FileText
- Download
- Upload
- Eye
- CheckCircle2
- AlertTriangle
- Ban
- Settings
- LayoutDashboard
- LogOut

Aturan:

- Jangan menggunakan emoji untuk action icon.
- Ikon selalu disertai label pada action penting.
- Ukuran umum 18–20px.
- Ikon stat card 22–24px.

---

# 9. Button System

## Primary

Background:

```css
#276453
```

Text:

```css
#FFFFFF
```

Contoh:

```text
Daftar Sekarang
Buat Event
Verifikasi Pembayaran
Submit Penilaian
```

---

## Secondary

```text
White background
Gray border
Dark text
```

Contoh:

```text
Lihat Detail
Simpan Draft
Kembali
```

---

## Danger

```text
#B42318
```

Contoh:

```text
Diskualifikasi
Hapus Event
Batalkan
```

---

## Ghost

Tanpa background.

Digunakan untuk:

- Detail.
- Edit minor.
- Navigation action.

---

# 10. Form Design

Input:

- Label di atas.
- Helper text opsional.
- Error di bawah input.
- Jangan menggunakan placeholder sebagai pengganti label.

Contoh:

```text
Nama Burung
[Panglima                         ]

Nama yang digunakan saat perlombaan.
```

Error:

```text
Nomor telepon wajib diisi.
```

Input height:

```text
44–48px
```

Textarea:

```text
minimum 120px
```

---

# 11. Landing Page

Route:

```text
/
```

Landing diposisikan sebagai website resmi brand dan event organizer GantangScore, bukan product-marketing page untuk software. Hierarki copy harus menjual kualitas kompetisi, agenda berikutnya, pengalaman peserta, dan kredibilitas penyelenggara. Informasi sistem cukup menjadi pendukung kepercayaan.

Landing mengambil pola:

```text
Navbar
↓
Hero
↓
Trust / Event Metrics
↓
Event Sedang Berlangsung
↓
Event Akan Datang
↓
Mengapa GantangScore
↓
Cara Kerja
↓
Juri / Sistem Penilaian
↓
Event Selesai
↓
CTA Pendaftaran
↓
Footer
```

---

# 12. Landing Navbar

Desktop:

```text
[GantangScore]

Beranda
Event
Cara Kerja
Hasil Lomba
Tentang

                        [Masuk] [Daftar]
```

Sticky:

```text
top: 0
```

Saat scroll:

```text
background: rgba(255,255,255,.94)
backdrop blur ringan
border-bottom
```

Mobile:

```text
Logo
Menu Button
```

Menu menggunakan slide-over.

---

# 13. Hero Landing

## Layout

Desktop:

```text
┌────────────────────────────┬─────────────────────────┐
│ Content                    │ Event Highlight         │
│                            │ / Competition Visual    │
│ Badge                      │                         │
│ H1                         │                         │
│ Description                │                         │
│ CTA                        │                         │
│ Trust metrics              │                         │
└────────────────────────────┴─────────────────────────┘
```

---

## Content

Badge:

```text
Event Organizer Burung Berkicau
```

H1:

```text
Panggung Berkelas untuk
Suara Terbaik.
```

Subheading:

```text
GantangScore menghadirkan kompetisi burung berkicau
yang tertib, kompetitif, dan layak dikenang.
```

CTA:

```text
[Lihat Agenda Event] [Kenal Lebih Dekat]
```

Jika user login peserta:

```text
[Dashboard Saya]
```

---

## Hero Visual

Gunakan card yang merepresentasikan event, bukan ilustrasi abstrak.

Contoh:

```text
LIVE

Murai Batu Utama
Albab Cup 2026

Penjurian
██████████████░░ 76%

23 / 30 peserta selesai

1   Panglima      Provisional
2   Samurai       Provisional
3   Raja          Provisional
```

Hasil tidak perlu menampilkan semua nilai pada hero.

---

# 14. Hero Metrics

Empat metric maksimal.

```text
24+
Event Terselenggara

1.2K+
Peserta Terdaftar

98%
Penilaian Terdigitalisasi

Realtime
Progress Penjurian
```

Jika data aktual belum tersedia, gunakan copy generik pada development.

Jangan menampilkan angka palsu pada production.

---

# 15. Section Event Sedang Berlangsung

Heading:

```text
Sedang Berlangsung

Ikuti jalannya kompetisi secara realtime.
```

Card event terdiri dari:

```text
[Event Banner]

LIVE
Albab Bird Competition 2026

Semarang, Jawa Tengah
16 September 2026

4 Kelas Aktif
128 Peserta

[████████████░░░] 78%

[Lihat Event]
```

Card maksimal 3 kolom desktop.

Mobile 1 kolom.

---

# 16. Event Card Component

Fields:

- Image.
- Event name.
- Status.
- Date.
- Location.
- Registration starting price.
- Number of classes.
- Number of participants.
- CTA.

Status:

```text
AKAN DATANG
SEDANG BERLANGSUNG
SELESAI
DIBATALKAN
```

---

# 17. Upcoming Events

Heading:

```text
Event Berikutnya

Temukan kompetisi dan kelas yang sesuai untuk burung Anda.
```

Filter:

```text
Semua
Murai Batu
Kacer
Cucak Hijau
Lainnya
```

Optional filter:

- Tanggal.
- Lokasi.

---

# 18. Why GantangScore

Layout:

6 cards.

```text
Penjurian Digital
Nilai juri tercatat langsung melalui sistem.

TOPSIS Ranking
Ranking dihitung secara konsisten menggunakan metode TOPSIS.

Realtime Progress
Pantau progres kompetisi tanpa refresh manual.

Multi Kelas
Satu event dapat memiliki banyak kelas dan hadiah.

Pendaftaran Terintegrasi
Pendaftaran dan verifikasi pembayaran dalam satu platform.

Hasil Transparan
Hasil final dan pemenang dapat diakses melalui halaman event.
```

---

# 19. How It Works

Gunakan 4 langkah.

```text
01
Pilih Event

02
Daftarkan Burung

03
Admin Verifikasi

04
Ikuti Kompetisi & Lihat Hasil
```

Desktop horizontal.

Mobile vertical timeline.

---

# 20. Sistem Penilaian Section

Tujuan section:

Memberikan pemahaman singkat tentang penilaian tanpa membuat landing terlalu akademik.

Heading:

```text
Penilaian yang Terstruktur
```

Criteria:

```text
Irama Lagu          40%
Durasi Kerja        20%
Volume              20%
Gaya                10%
Fisik               10%
```

Visual:

progress bars atau weighted cards.

Copy:

```text
Nilai dari seluruh juri dirata-ratakan terlebih dahulu.
Hasilnya kemudian digunakan dalam perhitungan TOPSIS
untuk menentukan ranking pada setiap kelas.
```

CTA:

```text
Pelajari Sistem Penilaian
```

---

# 21. Judge Trust Section

Section ini mengadaptasi konsep "team" menjadi sistem juri.

Heading:

```text
Dinilai oleh Juri yang Ditugaskan
```

Card juri:

```text
[Avatar]

Budi Santoso
Juri Murai Batu

Kelas:
Murai Batu Utama
Murai Batu Eksekutif
```

Jangan menampilkan kontak pribadi.

---

# 22. Live Competition Highlight

Section kontras menggunakan primary-900.

Background:

```text
#173A31
```

Text:

white.

Content:

```text
Kompetisi Sedang Berlangsung?

Pantau progres penjurian, peserta yang tampil,
dan status setiap kelas secara realtime.

[Lihat Live Event]
```

Di kanan dapat ditampilkan mini live card.

---

# 23. Completed Events

Card:

```text
SELESAI

Albab Cup 2026

5 Kelas
156 Peserta

Juara Utama:
Panglima

[Lihat Hasil]
```

Jangan menjadikan satu overall winner jika event hanya mempunyai pemenang per kelas.

---

# 24. CTA Registration

Background:

Primary-50.

Heading:

```text
Siap Mengikuti Kompetisi Berikutnya?
```

Text:

```text
Buat akun peserta, daftarkan burung Anda,
pilih kelas, dan kirim bukti pembayaran.
```

CTA:

```text
[Daftar Sebagai Peserta]
```

Secondary:

```text
[Lihat Semua Event]
```

---

# 25. Footer

Columns:

```text
GantangScore
Tentang singkat.

Platform
- Event
- Hasil Lomba
- Cara Kerja

Peserta
- Daftar
- Login
- Dashboard

Informasi
- Ketentuan
- FAQ
- Kontak
```

Bottom:

```text
© GantangScore.
Sistem Penjurian Kompetisi Gantangan Burung.
```

---

# 26. Event Detail Public Page

Route:

```text
/events/{slug}
```

Layout:

```text
Event Hero
↓
Primary Event Info
↓
Registration CTA
↓
Classes
↓
Prizes
↓
Participants
↓
Schedule
↓
Live Progress / Results
↓
Location
↓
Rules
```

---

# 27. Event Detail Hero

Content:

```text
SEDANG BERLANGSUNG

Albab Bird Competition 2026

16 September 2026
Semarang, Jawa Tengah

128 peserta • 5 kelas

[Lihat Live Progress]
```

Upcoming:

```text
[Daftar Sekarang]
```

Completed:

```text
[Lihat Hasil]
```

---

# 28. Class Card

```text
Murai Batu Utama

Murai Batu
Rp150.000
32 / 40 peserta

Juara 1
Sepeda Motor

Juara 2
Rp5.000.000

[Detail Kelas]
```

Status badge:

```text
Pendaftaran Dibuka
Penuh
Berlangsung
Penjurian Selesai
```

---

# 29. Public Live Page

Route:

```text
/events/{event}/live
```

Top:

```text
LIVE
Albab Cup 2026

Last updated: realtime
```

Tabs:

```text
Overview
Murai Batu Utama
Murai Batu Eksekutif
Kacer A
```

Per kelas:

```text
Penjurian
48 / 60 selesai

████████████████░░░ 80%
```

Participant grid:

```text
No. 17

[Bird Image]

Panglima
Budi Santoso

3 / 3 Juri
SELESAI
```

Jika DQ:

```text
DISKUALIFIKASI
```

Tidak tampilkan alasan sensitif secara panjang di card.

---

# 30. Public Result Page

Route:

```text
/events/{event}/results
```

Tabs per class.

Hero ranking:

```text
JUARA 1

Panglima
Budi Santoso

TOPSIS
0.8234
```

Ranking berikutnya:

```text
2 Samurai    0.7511
3 Raja       0.6878
```

Public result hanya menunjukkan hasil berstatus FINAL.

---

# 31. Authentication

Pages:

```text
/login
/register
/forgot-password
```

Layout:

Desktop split:

```text
Brand / Event visual | Form
```

Mobile:

Form only.

Login:

```text
Masuk ke GantangScore

Email
Password

[Masuk]

Belum memiliki akun?
[Daftar sebagai peserta]
```

Owner, admin, dan juri tidak memiliki public registration khusus.

---

# 32. Dashboard Global Layout

Semua role menggunakan shell yang sama.

Desktop:

```text
┌──────────────┬────────────────────────────────────┐
│ Sidebar      │ Topbar                             │
│              ├────────────────────────────────────┤
│              │ Page Content                       │
│              │                                    │
│              │                                    │
└──────────────┴────────────────────────────────────┘
```

Sidebar:

```text
width: 260px
```

Collapsed:

```text
72px
```

Topbar:

```text
height: 68px
```

Content:

```text
max-width: none
padding: 24–32px
```

---

# 33. Dashboard Sidebar

Brand:

```text
GantangScore
```

Role badge:

```text
OWNER
ADMIN
JURI
PESERTA
```

Bottom:

- Profile.
- Logout.

Mobile:

Sidebar menjadi slide-over drawer.

---

# 34. Topbar

Isi:

```text
Breadcrumb

                    Realtime indicator
                    User avatar
                    User name
```

Contoh:

```text
Event / Albab Cup / Murai Batu Utama

● Realtime Connected
Danu Candra
```

Status realtime:

```text
Green: Connected
Amber: Reconnecting
Gray: Offline fallback
```

---

# 35. Common Dashboard Components

Gunakan komponen berikut konsisten:

- StatCard
- StatusBadge
- DataTable
- FilterBar
- SearchInput
- EmptyState
- ActivityFeed
- Timeline
- ProgressCard
- EventCard
- ParticipantCard
- JudgeCard
- RankingCard
- ConfirmationModal
- Drawer
- Toast
- Pagination
- FileUploader

---

# 36. Owner Dashboard

## Sidebar Owner

```text
Dashboard
Event
Admin Event
Aktivitas
Profil
```

Tidak perlu terlalu banyak menu.

Detail pengelolaan kelas dan peserta dilakukan melalui event context.

---

# 37. Owner Dashboard Home

Header:

```text
Selamat datang, Owner

Kelola event dan pantau seluruh kompetisi dari satu tempat.
```

Primary CTA:

```text
[+ Buat Event]
```

Stats:

```text
12
Total Event

3
Sedang Berlangsung

5
Event Mendatang

8
Admin Aktif
```

---

## Main Grid

Left 2/3:

```text
Event Terbaru
```

Right 1/3:

```text
Aktivitas Terbaru
```

Event row:

```text
Albab Cup 2026
ONGOING
5 kelas
128 peserta
3 admin

[Lihat]
```

---

# 38. Owner Event List

Route:

```text
/owner/events
```

Top:

```text
Event
Kelola seluruh event.

[+ Buat Event]
```

Filters:

```text
Search
Status
Date
```

Table:

```text
Event
Tanggal
Lokasi
Kelas
Peserta
Admin
Status
Action
```

Mobile → card.

---

# 39. Owner Create Event

Gunakan step form.

```text
1 Informasi
2 Jadwal
3 Lokasi
4 Publikasi
```

Jangan menampilkan satu form sangat panjang.

Sticky footer:

```text
[Batal] [Simpan Draft] [Lanjut]
```

---

# 40. Owner Assign Admin

Panel:

```text
Admin Event

[Search existing user]

Assigned:
✓ Ahmad
✓ Siti

[+ Buat Akun Admin]
```

Gunakan modal untuk create account.

---

# 41. Admin Dashboard

## Sidebar

```text
Dashboard

EVENT AKTIF
Albab Cup 2026 ▼

Overview
Kelas
Peserta
Pembayaran
Juri
Penjurian
Hasil
Hadiah
Pengaturan

Aktivitas
Profil
```

Jika admin memiliki lebih dari satu event, gunakan event switcher pada sidebar.

---

# 42. Admin Dashboard Home

Header:

```text
Albab Bird Competition 2026

SEDANG BERLANGSUNG
16 September 2026
```

Stats:

```text
128
Peserta

118
Terverifikasi

5
Kelas

12
Juri
```

---

## Operational Alerts

Card hanya tampil jika diperlukan.

Contoh:

```text
12 pembayaran menunggu verifikasi

[Verifikasi Sekarang]
```

```text
Murai Batu Utama
2 juri belum menyelesaikan penilaian.
```

---

# 43. Admin Event Progress

Card:

```text
Progress Event

Registration     100%
Payment           92%
Judging           74%
Finalized         20%
```

Gunakan horizontal progress.

---

# 44. Admin Classes Page

Route:

```text
/admin/events/{event}/classes
```

Table:

```text
Kelas
Jenis Burung
Jadwal
Harga
Peserta
Juri
Status
Action
```

Primary:

```text
[+ Tambah Kelas]
```

Row click membuka detail kelas.

---

# 45. Admin Class Detail

Tabs:

```text
Overview
Peserta
Juri
Penjurian
Hadiah
Hasil
```

Overview:

```text
Murai Batu Utama

Status
ONGOING

Peserta
30 / 40

Juri
3

Biaya
Rp150.000

Jadwal
10:00–11:30
```

---

# 46. Admin Participants Page

Top filters:

```text
Search peserta / burung
Kelas
Payment Status
Registration Status
```

Table:

```text
No.
Peserta
Burung
Kelas
Pembayaran
Status
Nomor Gantangan
Action
```

Bulk action hanya jika aman.

Jangan menyediakan bulk diskualifikasi.

---

# 47. Admin Payment Verification

Layout desktop:

```text
┌────────────────────┬─────────────────────────┐
│ Queue              │ Proof Preview           │
│                    │                         │
│ Budi               │ image                   │
│ Panglima           │                         │
│ Rp150.000          │                         │
│ WAITING            │ Detail                  │
│                    │                         │
│                    │ [Tolak] [Verifikasi]    │
└────────────────────┴─────────────────────────┘
```

Mobile:

Card → detail screen.

Reject membuka modal:

```text
Alasan Penolakan
[                         ]

[Batalkan] [Tolak Pembayaran]
```

---

# 48. Admin Judges Page

Card/table:

```text
Juri
Email
Kelas Ditugaskan
Progress Hari Ini
Status
```

Action:

```text
Assign Kelas
Detail
```

---

# 49. Admin Judging Monitor

Halaman penting untuk realtime.

Header:

```text
Monitoring Penjurian

● Realtime
```

Class selector.

Summary:

```text
Murai Batu Utama

30 peserta
3 juri

67 / 90 evaluasi selesai
74%
```

---

## Judge Progress

```text
Juri A
24 / 30
████████████████░░ 80%

Juri B
23 / 30
███████████████░░░ 77%

Juri C
20 / 30
█████████████░░░░░ 67%
```

---

## Participant Status Table

```text
No.
Burung
Juri A
Juri B
Juri C
Status
```

Cell:

```text
✓
Pending
DQ
```

---

# 50. Admin Result / TOPSIS Page

Implementasi Tahap 7 memakai kartu hasil responsif dengan detail rata-rata/jarak yang dapat dibuka. Banner selalu membedakan PROVISIONAL dan FINAL, menampilkan progres serta waktu perhitungan. Tombol hitung ulang membaca database, bukan mengandalkan WebSocket. Finalisasi memakai dialog native dengan fallback inline dan konfirmasi ketik `FINALISASI`.

Panel draw hanya muncul untuk kelompok TOPSIS seri. Admin memilih urutan hasil lempar dadu nyata (tertinggi lebih dahulu), mencatat hasil/kejadian, dan mencentang konfirmasi. Tidak ada tombol simulasi dadu atau edit ranking peserta non-seri. Keputusan tersimpan hanya-baca; FINAL menampilkan peringkat resmi dan penanda bahwa draw diselesaikan di lokasi. Catatan undian internal tidak dipublikasikan.

Penggantian Juri absen tersedia di panel penugasan, hanya saat belum ada nilai submitted dari Juri tersebut. Riwayat penugasan lama tetap tampil. UI menyebut bahwa draft tidak diwariskan dan pengganti wajib menilai sendiri. Kelas final tidak menyediakan tindakan nilai, pelanggaran, penugasan, atau perubahan hadiah/informasi kelas.

Header:

```text
Hasil TOPSIS
Murai Batu Utama

PROVISIONAL
```

Summary callout:

```text
67 / 90 penilaian sudah masuk.
Ranking masih dapat berubah.
```

Table:

```text
Rank
Burung
Pemilik
Irama
Durasi
Volume
Gaya
Fisik
TOPSIS
Status
```

Top 3 dapat diberikan medal accent.

---

## Finalize Card

Bottom:

```text
Finalisasi Hasil

Pastikan semua penilaian yang diwajibkan
sudah selesai sebelum memfinalisasi hasil.

[Finalisasi Hasil]
```

Danger confirmation:

```text
Hasil akan dipublikasikan sebagai hasil resmi.

Ketik:
FINALISASI

[Batalkan] [Finalisasi]
```

---

# 51. Admin Prize Management

Per class:

```text
Juara 1
Barang
Sepeda Motor

Juara 2
Uang
Rp5.000.000

Juara 3
Uang
Rp3.000.000
```

Action:

```text
Tambah Hadiah
Edit
Hapus
```

Gunakan drag ordering bila diperlukan pada V2, bukan MVP.

---

# 52. Juri Dashboard

Juri harus memiliki UI paling sederhana.

Sidebar:

```text
Dashboard
Penugasan
Penilaian
Riwayat
Profil
```

Fokus utama:

```text
Mulai / Lanjutkan Penilaian
```

---

# 53. Juri Home

Header:

```text
Selamat Bertugas

Albab Bird Competition 2026
```

Today's assignment:

```text
Murai Batu Utama

10:00–11:30
30 Peserta

18 / 30 selesai

[Lanjutkan Penilaian]
```

---

# 54. Juri Class Participant List

Route:

```text
/judge/classes/{class}/judging
```

Top:

```text
Murai Batu Utama

18 / 30 selesai

[Search nomor gantangan]
```

Cards:

```text
No. 17

Panglima
Budi Santoso

Status:
BELUM DINILAI

[Nilai]
```

Completed:

```text
✓ SUBMITTED
```

DQ:

```text
DISKUALIFIKASI
```

---

# 55. Juri Scoring Screen

Implementasi Tahap 5 memakai radio berlabel angka: lima kolom pada layar kecil dan sepuluh kolom pada layar lebih lebar, dengan area sentuh minimal 44px. Kontrol dapat diakses lewat keyboard, memiliki penanda fokus, dan menampilkan pesan validasi per kriteria. Footer aksi menampilkan Simpan draft dan Submit nilai; halaman terkirim hanya menampilkan nilai tersimpan. Penanganan peringatan/DQ ditambahkan pada Tahap 6.

Ini adalah layar paling penting.

Mobile-first.

Header sticky:

```text
← Murai Batu Utama

No. 17
Panglima
```

Photo kecil.

---

## Score Form

Setiap kriteria menggunakan score selector besar:

```text
Irama Lagu
Bobot 40%

1 2 3 4 5 6 7 8 9 10
                [9]
```

Alternatif desktop:

number segmented control.

Jangan menggunakan slider karena angka presisi harus mudah dipilih.

---

## Score Summary

```text
Irama           9
Durasi          8
Volume          9
Gaya            7
Fisik           8
```

Footer sticky:

```text
[Peringatan]
[Simpan Draft]
[Submit Nilai]
```

Diskualifikasi ditempatkan di overflow/action danger agar tidak mudah salah klik.

---

# 56. Juri Warning Flow

Tap:

```text
Peringatan
```

Modal:

```text
Berikan Peringatan

Alasan
( ) Berteriak
( ) Memprovokasi peserta lain
( ) Mengganggu jalannya lomba
( ) Lainnya

Catatan
[                         ]

[Batalkan] [Berikan Peringatan]
```

Jika sudah punya warning:

```text
Peserta ini telah memiliki 1 peringatan.
Pelanggaran berikutnya dapat menyebabkan diskualifikasi.
```

---

# 57. Juri Disqualification Flow

Action danger:

```text
Diskualifikasi Peserta
```

Modal full attention:

```text
Diskualifikasi

Burung:
Panglima

Alasan:
[ Terlambat menggantangkan ▼ ]

Catatan *
[                             ]

Tindakan ini akan mengeluarkan peserta
dari perhitungan ranking kelas.

[Batalkan] [Diskualifikasi]
```

Require explicit confirmation.

Implementasi Tahap 6 memakai dialog native dengan fallback formulir inline jika JavaScript tidak tersedia. DQ langsung berada di bagian danger yang dapat dibuka secara sengaja. Dialog menampilkan burung/nomor gantangan, alasan, catatan kejadian, dan checkbox konfirmasi wajib untuk setiap DQ, termasuk eskalasi peringatan. Saat sudah diperingatkan, tombol dan dialog menyebutkan bahwa pelanggaran berikutnya **mengakibatkan** DQ, bukan hanya berpotensi DQ. Alasan `Lainnya` membutuhkan penjelasan.

Riwayat internal tersedia pada lembar peserta Juri, detail peserta Admin, daftar pelanggaran event Admin, serta monitoring event milik Owner. Riwayat menggunakan kartu responsif, badge berteks, waktu, petugas, alasan, dan catatan yang di-escape. Peserta hanya melihat status peringatan/DQ miliknya tanpa alasan internal, catatan, atau identitas Juri. Nilai submitted tetap read-only meskipun tombol pelanggaran masih tersedia selama penjurian berlangsung.

---

# 58. Juri Submitted State

Setelah submit:

```text
✓ Nilai Berhasil Dikirim

Panglima
No. 17

Irama      9
Durasi     8
Volume     9
Gaya       7
Fisik      8

[Lanjut Peserta Berikutnya]
```

Juri tidak melihat nilai juri lain.

---

# 59. Peserta Dashboard

Sidebar:

```text
Dashboard
Event
Burung Saya
Pendaftaran
Piagam
Profil
```

---

# 60. Participant Home

Greeting:

```text
Halo, Budi

Pantau pendaftaran dan event yang sedang Anda ikuti.
```

Primary summary:

```text
1
Event Aktif

2
Burung

1
Pendaftaran Terverifikasi

1
Piagam
```

---

# 61. Participant Active Competition

Card besar:

```text
SEDANG BERLANGSUNG

Albab Cup 2026

Murai Batu Utama

Panglima
No. Gantangan 17

Penjurian
3 / 3 Juri

SELESAI

[Lihat Event]
```

---

# 62. Bird Management

Route:

```text
/participant/birds
```

Cards:

```text
[Photo]

Panglima
Murai Batu

2 Event
1 Juara

[Detail] [Edit]
```

Primary:

```text
[+ Tambah Burung]
```

---

# 63. Add Bird Form

Fields:

```text
Nama Burung *
Jenis Burung *
Foto *
Deskripsi
```

Image upload:

- Drag & drop desktop.
- Choose photo mobile.
- Preview.
- Remove/reupload.

---

# 64. Participant Event Registration

Gunakan wizard.

```text
1 Kelas
2 Burung
3 Data
4 Pembayaran
```

---

## Step 1

Cards class.

```text
Murai Batu Utama

Rp150.000
32 / 40

Hadiah Utama
Sepeda Motor

[Pilih Kelas]
```

---

## Step 2

Pilih burung yang kompatibel.

```text
Panglima
Murai Batu

[Pilih]
```

Tidak tampilkan burung dengan tipe yang tidak kompatibel kecuali dengan label tidak memenuhi syarat.

---

## Step 3

Summary:

```text
Event:
Albab Cup 2026

Kelas:
Murai Batu Utama

Burung:
Panglima

Biaya:
Rp150.000
```

CTA:

```text
[Lanjut Pembayaran]
```

---

# 65. Upload Payment

Card instruction:

```text
Pembayaran Pendaftaran

Total:
Rp150.000
```

Bank details configurable.

Implementasi Tahap 4 membaca bank, nomor rekening, nama penerima, dan instruksi dari konfigurasi `gantangscore.payment`. Jika belum lengkap, tampilkan arahan menghubungi panitia tanpa rekening contoh. Nominal transfer diisi dalam Rupiah bulat; Admin melihat peringatan jika nominal berbeda dari snapshot biaya pendaftaran, lalu memutuskan berdasarkan pemeriksaan manual.

Pada penolakan pembayaran, modal native membuka form alasan wajib. Form tetap tersedia secara inline jika JavaScript tidak aktif. Tombol pengiriman menampilkan proses dan dinonaktifkan selama permintaan berjalan. Riwayat pembayaran dipaginasi dan selalu mempertahankan percobaan yang ditolak.

Upload:

```text
Upload Bukti Pembayaran
PNG, JPG, WEBP
Maks. 5 MB
```

CTA:

```text
[Kirim Bukti Pembayaran]
```

After:

```text
MENUNGGU VERIFIKASI
```

---

# 66. Participant Registration Detail

Timeline:

```text
✓ Pendaftaran Dibuat
16 Sep • 10:12

✓ Bukti Pembayaran Dikirim
16 Sep • 10:18

● Menunggu Verifikasi
```

Verified:

```text
✓ Pembayaran Terverifikasi

[Download Bukti Pendaftaran]
```

Rejected:

```text
Pembayaran Ditolak

Alasan:
Bukti transfer tidak terbaca.

[Upload Bukti Baru]
```

---

# 67. Registration Proof

Preview card:

```text
BUKTI PENDAFTARAN

Albab Cup 2026

Peserta
Budi Santoso

Burung
Panglima

Kelas
Murai Batu Utama

No. Pendaftaran
REG-2026-00128

Status
VERIFIED
```

Actions:

```text
[Lihat PDF]
[Download]
```

---

# 68. Participant Certificates

Grid/list:

```text
PIAGAM

Juara 1
Murai Batu Utama

Albab Cup 2026
Panglima

16 September 2026

[Lihat] [Download]
```

---

# 69. Empty States

Empty state harus memberikan next action.

Contoh:

```text
Belum Ada Burung

Tambahkan profil burung terlebih dahulu
sebelum mengikuti kompetisi.

[+ Tambah Burung]
```

Jangan hanya:

```text
No data.
```

---

# 70. Loading States

Dashboard:

Skeleton.

Button submit:

```text
Submitting...
```

Disable selama request berlangsung.

Realtime:

jangan full-page loading saat update kecil masuk.

---

# 71. Error States

## Network

```text
Tidak dapat terhubung ke server.

Data yang sudah tersimpan tetap aman.
[Coba Lagi]
```

---

## Reverb Offline

```text
Realtime terputus.
Mencoba menyambungkan kembali...
```

Dashboard tetap berfungsi menggunakan HTTP.

---

## 403

```text
Anda tidak memiliki akses ke halaman ini.
[Kembali ke Dashboard]
```

---

## 404

```text
Halaman tidak ditemukan.
```

---

# 72. Toast

Position:

```text
top-right desktop
bottom mobile
```

Contoh:

Success:

```text
✓ Pembayaran berhasil diverifikasi.
```

Warning:

```text
Nilai masih disimpan sebagai draft.
```

Danger:

```text
Gagal menyimpan data. Silakan coba lagi.
```

Maximum visible:

3.

Auto-close:

4–6 seconds.

Error penting jangan auto-close terlalu cepat.

---

# 73. Tables

Desktop menggunakan table.

Rules:

- Header sticky jika tabel panjang.
- Zebra striping tidak diperlukan.
- Gunakan divider lembut.
- Row hover.
- Actions di kanan.
- Status berupa badge.
- Pagination bawah.

Mobile:

convert ke cards.

---

# 74. Search & Filters

Pattern:

```text
[ Search...                         ]

[Status ▼] [Kelas ▼] [Tanggal ▼] [Reset]
```

Filter aktif tampil sebagai chips.

---

# 75. Modal

Gunakan modal hanya untuk:

- Confirm destructive action.
- Quick create.
- Short form.
- Warning/DQ.
- Finalisasi.

Jangan taruh form event panjang di modal.

---

# 76. Responsive Breakpoints

```text
sm:  640px
md:  768px
lg:  1024px
xl:  1280px
2xl: 1536px
```

Main dashboard target:

```text
desktop ≥ 1024px
```

Juri scoring dioptimalkan mulai 360px.

---

# 77. Mobile Bottom Action

Untuk halaman action-heavy seperti:

- Juri scoring.
- Registration wizard.
- Payment verification.

Gunakan sticky action bar:

```text
position: sticky/fixed
bottom: 0
```

Pastikan safe-area pada iOS.

---

# 78. Accessibility

Minimum:

- WCAG AA contrast.
- Semua input punya label.
- Semua action dapat dijalankan via keyboard.
- Focus state jelas.
- Icon-only button punya aria-label.
- Badge tidak hanya mengandalkan warna.
- Modal focus trap.
- Error message terkait input.
- Table punya semantic heading.
- Realtime updates tidak mengganggu screen reader secara berlebihan.

---

# 79. Motion

Gunakan:

```text
150–250 ms
ease-out
```

Allowed:

- Hover card.
- Collapse.
- Dropdown.
- Progress changes.
- Toast.
- Modal.

Avoid:

- Parallax.
- Bouncy animation.
- Continuous floating.
- Confetti setiap ranking berubah.
- Marquee.

Confetti boleh dipertimbangkan hanya saat hasil final / sertifikat V2, bukan dashboard operasional.

---

# 80. Charts

MVP hanya membutuhkan chart ringan.

Recommended:

### Admin

- Judging completion: horizontal progress.
- Participant per class: horizontal bar.
- Payment status: segmented bar / donut opsional.

### Owner

- Event by status: bar.
- Participants per event: line/bar.

Jangan menggunakan chart jika angka stat card sudah cukup.

---

# 81. TOPSIS UI Rules

TOPSIS harus dijelaskan dengan bahasa manusia.

Jangan menjadikan rumus matematika sebagai konten utama dashboard.

Admin result:

```text
Nilai TOPSIS
0.8234
```

Tooltip:

```text
Nilai preferensi TOPSIS.
Semakin tinggi nilainya, semakin tinggi ranking peserta.
```

Detail calculation dapat ditempatkan dalam:

```text
[Lihat Detail Perhitungan]
```

Drawer/modal.

---

# 82. TOPSIS Detail Drawer

```text
Detail Perhitungan

Rata-rata Kriteria
Irama       9.00
Durasi      8.33
Volume      9.00
Gaya        8.00
Fisik       8.33

Jarak Ideal Positif
0.0512

Jarak Ideal Negatif
0.2387

Preference
0.8234
```

Tidak perlu ditampilkan kepada peserta umum pada MVP kecuali diperlukan transparansi.

---

# 83. Status Dictionary

## Event

```text
DRAFT
UPCOMING
ONGOING
COMPLETED
CANCELLED
```

---

## Registration

```text
PENDING PAYMENT
WAITING VERIFICATION
VERIFIED
PAYMENT REJECTED
CANCELLED
DISQUALIFIED
```

---

## Class

```text
UPCOMING
ONGOING
JUDGING COMPLETED
FINALIZED
```

---

## Evaluation

```text
DRAFT
SUBMITTED
```

---

## Result

```text
PROVISIONAL
FINAL
```

---

# 84. Badge Mapping

```text
DRAFT
gray

UPCOMING
blue

ONGOING
green + pulse dot

WAITING
amber

VERIFIED
green

REJECTED
red

DISQUALIFIED
red

PROVISIONAL
amber

FINAL
primary / dark green

COMPLETED
gray-green
```

Pulse hanya pada LIVE/ONGOING.

---

# 85. Role Permission UX

UI tidak hanya menyembunyikan menu.

Backend tetap menjadi authorization source of truth.

Jika role tidak punya akses:

- Menu tidak ditampilkan.
- Direct URL tetap ditolak.
- Action tidak dirender.
- API/controller memakai policy/gate.

---

# 86. Component Naming Laravel Blade

Recommended:

```text
resources/views/components/
```

Struktur:

```text
components/
├── ui/
│   ├── button.blade.php
│   ├── badge.blade.php
│   ├── card.blade.php
│   ├── modal.blade.php
│   ├── input.blade.php
│   ├── select.blade.php
│   ├── textarea.blade.php
│   ├── table.blade.php
│   ├── empty-state.blade.php
│   ├── progress.blade.php
│   └── alert.blade.php
│
├── event/
│   ├── card.blade.php
│   ├── status.blade.php
│   └── live-progress.blade.php
│
├── participant/
│   ├── card.blade.php
│   └── registration-status.blade.php
│
├── judging/
│   ├── score-input.blade.php
│   ├── judge-progress.blade.php
│   └── evaluation-status.blade.php
│
└── ranking/
    ├── podium.blade.php
    └── table.blade.php
```

---

# 87. Layout Blade

```text
resources/views/layouts/
```

Recommended:

```text
app.blade.php
guest.blade.php
public.blade.php
dashboard.blade.php
```

Role-specific nav dapat menggunakan partial/component.

---

# 88. Suggested Public Pages

```text
/
 /events
 /events/{slug}
 /events/{slug}/live
 /events/{slug}/results
 /how-it-works
 /about

 /login
 /register
 /forgot-password
```

---

# 89. Owner Pages

```text
/owner/dashboard
/owner/events
/owner/events/create
/owner/events/{event}
/owner/events/{event}/edit
/owner/events/{event}/admins
/owner/activity
/owner/profile
```

---

# 90. Admin Pages

```text
/admin/dashboard
/admin/events/{event}

/admin/events/{event}/classes
/admin/events/{event}/classes/{class}

/admin/events/{event}/participants
/admin/events/{event}/payments
/admin/events/{event}/judges
/admin/events/{event}/judging
/admin/events/{event}/results
/admin/events/{event}/prizes
/admin/events/{event}/settings
/admin/activity
/admin/profile
```

---

# 91. Judge Pages

```text
/judge/dashboard
/judge/assignments
/judge/classes/{class}/judging
/judge/evaluations/{registration}
 /judge/history
 /judge/profile
```

---

# 92. Participant Pages

```text
/participant/dashboard
/participant/events

/participant/birds
/participant/birds/create
/participant/birds/{bird}/edit

/participant/registrations
/participant/registrations/create
/participant/registrations/{registration}

/participant/certificates
/participant/profile
```

---

# 93. Realtime UX Events

## ScoreSubmitted

Affected:

- Admin judging monitor.
- Public live progress.
- Provisional ranking.

Animation:

```text
progress width transition 200ms
row soft highlight 800ms
```

---

## ParticipantDisqualified

Affected:

- Juri participant list.
- Admin participant list.
- Admin TOPSIS.
- Public live participant status.

Do not show toast to all public visitors.

Update card status only.

---

## ResultFinalized

Public:

```text
HASIL RESMI TELAH TERSEDIA

[Lihat Hasil]
```

Admin:

```text
✓ Hasil berhasil difinalisasi.
```

---

# 94. Design of Live Indicator

Component:

```text
● LIVE
```

Green pulse dot.

Do not make entire badge flash.

---

# 95. File Upload UX

Upload proof/photo:

```text
┌──────────────────────────────────┐
│                                  │
│     Upload file                  │
│     Drag & drop atau pilih       │
│                                  │
│     JPG, PNG, WEBP • maks. 5 MB  │
│                                  │
└──────────────────────────────────┘
```

After:

```text
[thumbnail]
transfer.jpg
2.1 MB

[Ganti] [Hapus]
```

---

# 96. Confirmation Patterns

Normal:

```text
Hapus data ini?
```

High risk:

- Diskualifikasi.
- Finalisasi.
- Remove event admin.
- Cancel event.

Gunakan modal dengan deskripsi dampak.

---

# 97. Date & Currency

Locale:

```text
id-ID
```

Tanggal:

```text
16 September 2026
```

Waktu:

```text
10.30 WIB
```

Currency:

```text
Rp150.000
Rp5.000.000
```

Jangan:

```text
IDR 150000
```

pada UI peserta.

---

# 98. Content Tone

Bahasa:

- Ringkas.
- Profesional.
- Tidak terlalu formal.
- Tidak menggunakan istilah teknis tanpa penjelasan.

Contoh bagus:

```text
Pembayaran menunggu verifikasi admin.
```

Bukan:

```text
Status transaksi Anda saat ini adalah WAITING_VERIFICATION.
```

---

# 99. Privacy Rules UI

Public landing boleh menampilkan:

- Nama burung.
- Nama pemilik sesuai kebijakan event.
- Foto burung.
- Nomor gantangan.
- Ranking.

Jangan tampilkan:

- Nomor telepon peserta.
- Email.
- Alamat rumah lengkap.
- Bukti pembayaran.
- Internal note juri.

---

# 100. MVP Screen Priority

Implementasi UI sebaiknya mengikuti urutan:

## Phase 1 — Foundation

```text
Design Tokens
Components
Authentication
Dashboard Shell
```

## Phase 2 — Event

```text
Owner Dashboard
Event Management
Admin Assignment
Class Management
```

## Phase 3 — Participant

```text
Landing
Event Detail
Bird Management
Registration
Payment
```

## Phase 4 — Judging

```text
Judge Assignment
Judge Dashboard
Scoring
Warning
DQ
```

## Phase 5 — TOPSIS

```text
Admin Monitor
TOPSIS Ranking
Finalize
Public Result
```

## Phase 6 — Documents

```text
Registration PDF
Certificate PDF
```

## Phase 7 — Realtime

```text
Laravel Reverb
Live Progress
Provisional Ranking
Realtime Status
```

---

# 101. Final UI Quality Checklist

Sebelum halaman dianggap selesai:

### Visual

- [ ] Menggunakan design tokens.
- [ ] Spacing konsisten.
- [ ] Tidak ada random color.
- [ ] Tidak ada random font size.
- [ ] Shadow ringan.
- [ ] Radius konsisten.
- [ ] Icon style konsisten.

### UX

- [ ] Primary CTA jelas.
- [ ] Loading state tersedia.
- [ ] Empty state tersedia.
- [ ] Error state tersedia.
- [ ] Success feedback tersedia.
- [ ] Destructive action dikonfirmasi.
- [ ] Form error jelas.

### Responsive

- [ ] 360px.
- [ ] 390px.
- [ ] 768px.
- [ ] 1024px.
- [ ] 1280px.
- [ ] 1440px.

### Accessibility

- [ ] Focus visible.
- [ ] Label form tersedia.
- [ ] Contrast AA.
- [ ] Keyboard navigation.
- [ ] Icon buttons mempunyai accessible label.

### Realtime

- [ ] Reverb disconnected state.
- [ ] Reconnect state.
- [ ] HTTP fallback.
- [ ] Tidak ada data ganda.
- [ ] Update tidak memindahkan focus user.

---

# 102. Core Design Summary

## Landing

```text
Modern Competition Website
+
Professional Event Platform
+
Warm Natural Visual Language
```

Struktur:

```text
Hero
→ Live Event
→ Upcoming Event
→ Features
→ How It Works
→ Judging System
→ Judges
→ Results
→ Registration CTA
```

---

## Owner

Fokus:

```text
Multi Event
Admin Assignment
High-level Monitoring
```

---

## Admin

Fokus:

```text
Event Operations
Registration
Payment Verification
Judge Assignment
Realtime Judging
TOPSIS
Result Finalization
```

---

## Juri

Fokus:

```text
Fast Scoring
Low Cognitive Load
Warnings
Disqualification
```

---

## Peserta

Fokus:

```text
Find Event
Register Bird
Payment Status
Competition Status
Results
Certificates
```

---

# 103. Final Design Principle

Jika terdapat konflik antara visual yang menarik dan kejelasan operasional:

```text
CLARITY WINS.
```

Jika terdapat konflik antara banyak informasi dan informasi yang relevan:

```text
PRIORITY WINS.
```

Jika terdapat konflik antara realtime animation dan fokus pengguna:

```text
FOCUS WINS.
```

Jika terdapat konflik antara desain desktop dan kemudahan juri/peserta di smartphone:

```text
MOBILE USABILITY WINS.
```

GantangScore harus terlihat profesional saat dibuka publik, cepat saat digunakan panitia, dan sangat sederhana saat digunakan juri di tengah kompetisi.

# Implementasi UI Tahap 8 — 29 Oktober 2026

Panel progres memakai komponen Blade `live-class`: badge koneksi dengan teks, progressbar berlabel, jumlah evaluasi/DQ, waktu pembaruan WIB, serta tautan hasil resmi ketika FINAL. Halaman event publik, detail event Owner, monitor/ranking Admin, dan kelas/form Juri memakai bahasa visual forest/gold yang ada. Fallback polling 20 detik tidak me-reload formulir. Perubahan DQ/FINAL menonaktifkan tombol relevan dan menjelaskan perubahan; nilai yang sedang diketik tetap ada.

Tabel ranking internal diperbarui melalui fragment Blade. Form keputusan draw/finalisasi tetap memerlukan muat ulang untuk meninjau snapshot terbaru. Publik hanya melihat progres sampai FINAL tersedia. UI tidak menampilkan raw score Juri lain.

Menu Piagam peserta sudah aktif di `/participant/certificates`. Kartu responsif satu/dua/tiga kolom, nama panjang break-words, empty state, status pending, feedback, serta tombol lihat/cetak dan unduh pada PDF yang tersedia. Notifikasi privat memperbarui kesiapan kartu tanpa membuka storage path.

PDF memakai template `pdf/certificate.blade.php`, A4 landscape, warna forest/gold, nomor dokumen, identitas peserta/burung, kelas/event, rank, tanggal dan penyelenggara. Nama panjang mengecil secara terukur. Tidak ada tanda tangan/stempel palsu; penambahan aset resmi menunggu review. Template normal dan nama panjang telah dirender serta diperiksa satu halaman. Pemeriksaan browser pada seluruh viewport 360–1440 px masih menjadi pekerjaan hardening Tahap 9.

# Hardening UI Tahap 9 — 29 Oktober 2026

Navigasi dashboard mobile sekarang mengeluarkan sidebar tertutup dari urutan fokus melalui inert/aria-hidden. Saat terbuka, sidebar menjadi dialog berlabel, konten belakang inert, fokus dibatasi dalam menu, serta Escape/tombol Tutup/backdrop mengembalikan fokus ke pemicu. Perpindahan breakpoint desktop membersihkan state modal. Tersedia skip link ke main, scroll sidebar untuk layar pendek, fallback navigasi tanpa JavaScript dan dukungan reduced motion.

Komponen form mendukung ID khusus untuk beberapa formulir dengan nama field sama, aria-invalid, serta gabungan referensi helper/error. Placeholder/label terkait diperjelas kontrasnya, dan nama panjang pada layar Juri membungkus. Copy fondasi/tahap pengembangan serta menu nonaktif “Segera” di dashboard Owner/Admin dihapus; alur event aktif tetap menjadi navigasi utama.

Perilaku navigasi pada enam breakpoint diuji dengan adapter DOM ringan dan Node; Blade/form diuji otomatis. Ini belum merupakan pemeriksaan visual responsive browser. Browser automation host tidak tersedia pada sesi Tahap 9; checklist visual akhir ada di `Testing.md` dan status terperinci di kontrak Tahap 9.

# Revisi Navigasi Dashboard dan Pemilihan Admin

Sesuai permintaan pengguna setelah Tahap 9, menu operasional dipusatkan di sidebar:

- Owner: Beranda, Event Saya, Buat Event, Admin Event, Pantau Penjurian. Admin Event/Pantau menampilkan daftar event milik Owner, lalu menuju bagian terkait pada detail event.
- Admin: Beranda, Semua Event, pemilih event, Ringkasan Event, Kelas Lomba, Peserta, Pembayaran, Juri, Monitor Penjurian, Hasil & Piagam, Hadiah, Pelanggaran, Pengaturan Event. Menu kelas/Juri/monitor/hasil/hadiah membuka pilihan kelas pada event aktif. Menu lain menuju halaman operasional yang sudah ada.
- Juri: Beranda, Penugasan, Penilaian, Riwayat Nilai; ketika membuka kelas tersedia pintasan Peserta Kelas Ini. Penilaian menampilkan kelas berlangsung, riwayat hanya kelas penugasan aktif yang memiliki evaluasi submitted milik Juri tersebut.
- Peserta: Beranda, Event, Burung Saya, Pendaftaran, Piagam, Profil.

Pemilih event Admin hanya memuat event penugasannya, maksimum 50 event terbaru ditambah event aktif bila lebih lama. Event lain tetap dapat dipilih dari Semua Event. Pemilihan menggunakan form GET dengan tombol eksplisit, sehingga tidak bergantung pada JavaScript; konteks pilihan disimpan dalam session dan diperiksa ulang terhadap assignment. Sidebar menandai halaman aktif dengan aria-current, bisa discroll tanpa menu/footer bertumpuk, serta mempertahankan perilaku fokus mobile Tahap 9.

Pada detail event Owner, penugasan Admin memakai select “Pilih akun Admin”, menampilkan nama dan Gmail/email akun yang sudah terdaftar. Hanya akun berperan ADMIN, aktif dan belum ditugaskan pada event itu yang ditawarkan. Pilihan dipaginasi 25 akun per halaman. Jika kosong, tombol dinonaktifkan dan pilihan membuat akun baru tetap tersedia. Form tetap mengirim email ke endpoint assignment yang sama; backend menolak akun tidak aktif/non-Admin dan event milik Owner lain meskipun request dimanipulasi.

Data navigasi disiapkan view composer/query; tidak ada query database di Blade. Tidak ada perubahan schema atau reset database pada revisi UI ini. Pengujian akses dan rendering otomatis ditambahkan; verifikasi screenshot browser belum tersedia karena host browser otomatis gagal terhubung.

# Revisi Pemilihan Nomor Gantangan oleh Peserta

Pada formulir daftar kelas, peserta memilih burung dan mengisi “Nomor gantangan pilihan”. Helper menjelaskan bahwa nomor langsung dipesan tanpa verifikasi Admin, harus berupa angka positif, unik pada kelas tersebut, dan terkunci setelah berhasil dipilih. Benturan ditampilkan sebagai error pada field dengan aria-invalid/aria-describedby, sementara burung dan nomor yang diketik tetap dipulihkan lewat old input. Pengguna dapat memilih nomor lain lalu mengirim ulang.

Pendaftaran lama yang nomornya kosong menyediakan kartu “Pilih nomor gantangan Anda” di detail pendaftaran sebelum penjurian dimulai. Setelah berhasil, kartu pengisian hilang dan nomor ditampilkan sebagai tersimpan/terkunci. Nomor tidak mengubah status verifikasi pembayaran. Tidak ada form Admin untuk menyetujui nomor.

# Banner pada Detail Event Dashboard

Detail event Owner dan Admin menampilkan komponen `event-banner` dengan gambar penuh memakai object-contain agar teks poster tidak terpotong, tinggi maksimum responsif, alt text nama event, serta tautan Ganti/Unggah banner menuju Edit Event. Jika banner_path kosong, tampil petunjuk “Banner event belum diunggah”, bukan ruang gambar kosong. Storage public dan URL banner yang sudah ada tetap digunakan; gambar privat pembayaran tidak dipublikasikan.

Pemeriksaan lintas halaman memperluas banner ke kartu event landing, daftar dan detail publik, daftar Owner/Admin, daftar event/agenda dashboard peserta, workspace event Owner, serta preview Edit Event. Komponen `event-artwork` menyamakan gambar object-contain dan fallback “Banner belum tersedia”. Detail publik menampilkan poster sebagai panel tersendiri tanpa opacity/lapisan gelap yang menyamarkan poster. Data featured event landing kini menyertakan banner_url. Halaman penjurian/pendaftaran yang menampilkan foto burung tetap memakai foto burung, bukan menggantinya dengan banner.

Tes HTTP terotorisasi memeriksa URL dan alt gambar pada seluruh halaman event di atas serta upload/penggantian/fallback/privasi draft. Browser automation host masih tidak dapat diakses pada pemeriksaan ini; tidak ada klaim verifikasi visual browser. Banner hanya tampil bila file sudah diunggah dan banner_path event tersimpan; file lama yang tidak terhubung ke event tidak dipasang otomatis.
