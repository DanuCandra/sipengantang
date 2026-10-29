# Tahap 4 - Payment & Verification

Status: selesai dan siap direview.

## Fitur

- Peserta mengirim nominal transfer dan bukti JPG/JPEG/PNG/WebP maksimal 5 MB dari detail pendaftarannya.
- Bukti menggunakan nama acak pada disk `private`, dengan akses melalui controller yang memeriksa kepemilikan atau pengelolaan event. Tidak ada URL bukti pada public storage.
- Pendaftaran `PENDING_PAYMENT` atau `PAYMENT_REJECTED` dapat mengirim satu bukti baru. Upload mengubah pembayaran dan pendaftaran menjadi `WAITING_VERIFICATION` secara bersamaan.
- Bukti baru menjadi baris `payment_submissions` baru. Percobaan yang ditolak beserta berkas dan alasan tetap tersimpan.
- Admin yang tercatat pada `event_admins` dapat memeriksa antrean, melihat bukti, memverifikasi, atau menolak dengan alasan wajib.
- Verifikasi mengisi peninjau/waktu peninjauan, mengubah pendaftaran menjadi `VERIFIED`, dan mengisi `verified_at`.
- Penolakan mengubah pendaftaran menjadi `PAYMENT_REJECTED` sehingga peserta dapat mengirim ulang.
- Peninjauan ulang terhadap percobaan yang telah diputuskan ditolak tanpa mengubah keputusan dan audit sebelumnya.
- Pembayaran pada kelas final/dibatalkan atau event dibatalkan tidak dapat diubah.
- Riwayat ditampilkan dengan pagination, dan payload user tidak dapat mengatur status atau identitas peninjau.
- Domain event dikirim setelah commit. Operasi HTTP berfungsi tanpa Reverb atau worker queue.
- Peserta terverifikasi dapat melihat dan mengunduh PDF bukti pendaftaran miliknya. Dokumen memuat event, peserta, burung, jenis, kelas, nomor pendaftaran, gantangan bila tersedia, tanggal, lokasi, dan status resmi.

## Transaksi dan keamanan

Semua penulisan pembayaran menggunakan transaksi dan urutan penguncian kelas, pendaftaran, lalu percobaan pembayaran. Ini melindungi satu percobaan aktif dan mencegah keputusan ganda. Berkas upload dibersihkan bila transaksi pengiriman gagal. Audit dan status terkait disimpan di transaksi yang sama. Tidak ada perubahan destruktif atau reset data lama.

Peran global Admin tidak memberi akses pembayaran event lain. Owner event dapat membaca bukti untuk monitoring, tetapi keputusan pembayaran tetap membutuhkan peran Admin dan penugasan event. Bukti/PDF menggunakan respons privat `no-store`; tidak menampilkan alamat pribadi atau bukti transfer pada dokumen resmi.

## Konfigurasi sebelum penggunaan

Isi konfigurasi berikut melalui environment deployment lalu muat ulang cache konfigurasi bila digunakan:

```dotenv
PAYMENT_BANK_NAME=
PAYMENT_ACCOUNT_NUMBER=
PAYMENT_ACCOUNT_NAME=
PAYMENT_INSTRUCTIONS=
```

Rekening berlaku pada konfigurasi penyelenggara aplikasi. Belum ada pengaturan rekening berbeda per event. Jika rekening belum lengkap, peserta diarahkan menghubungi panitia.

## Batas aturan bisnis

- Nominal transfer dibandingkan dengan `registration_fee_snapshot`, bukan harga kelas terkini. Perbedaan nominal ditandai untuk Admin; keputusan tetap manual karena toleransi kurang/lebih bayar belum ditetapkan.
- Kebijakan kuota dari Tahap 3 tetap berlaku: semua pendaftaran selain `CANCELLED` menahan slot. Batas waktu pembayaran dan pelepasan slot otomatis belum diputuskan.
- Tidak menambahkan gateway, notifikasi eksternal, pembatalan, refund, atau pembalikan keputusan pembayaran.
- PDF bukti pendaftaran dirender saat diminta dari data saat ini; snapshot final hasil kompetisi merupakan pekerjaan tahap berikutnya.

## Skenario review

1. Pastikan Owner mempublikasikan event dengan kelas dan menugaskan Admin.
2. Login sebagai Peserta, daftarkan burung, lalu buka detail pendaftaran.
3. Periksa total, kontak/rekening, lalu kirim nominal dan bukti gambar.
4. Login sebagai Admin, buka event lalu **Verifikasi pembayaran peserta**.
5. Buka bukti dan tolak dengan alasan. Pastikan peserta melihat alasan dan dapat upload ulang.
6. Verifikasi bukti baru. Pastikan kedua percobaan tetap ada dan pendaftaran menjadi terverifikasi.
7. Peserta membuka serta mengunduh PDF bukti pendaftaran.
8. Coba akun peserta lain dan Admin tanpa penugasan untuk memastikan akses ditolak.

## Verifikasi

Seluruh suite aplikasi lulus: 95 test dan 387 assertion. Laravel Pint, kompilasi Blade, dan build Vite berhasil. Migration pembayaran telah diterapkan pada database lokal tanpa menghapus data. Contoh PDF dirender dan diperiksa secara visual sebagai satu halaman A4. Paket `league/commonmark` juga diperbarui dari 2.10.1 ke 2.10.4 untuk memperbaiki advisory yang ditemukan saat audit dependensi.

Suite pembayaran mencakup upload privat, validasi gambar/nominal, penolakan dan upload ulang, riwayat berkas, status terminal, kelas final, keputusan berulang, rollback atomik, domain event setelah commit, akses per peran/event, escaping alasan, dan PDF asli beserta batas aksesnya. Pemeriksaan visual browser otomatis tidak tersedia pada sesi ini karena alat tidak dapat memastikan URL Chrome. Tata letak responsif masih perlu direview pengguna.

## Tahap berikutnya

**Tahap 5 - Penugasan Juri & Penilaian**: penugasan Juri per kelas, daftar peserta terverifikasi, form nilai 1-10 untuk lima kriteria, simpan draft, submit final yang tidak dapat diubah, dan perlindungan independensi nilai antarjuri.
