# Tahap 3 — Participant & Registration

Status: **selesai dan siap direview**

## Ruang lingkup yang aktif

- Publik dapat melihat daftar dan detail event yang sudah dipublikasikan.
- Halaman event publik menampilkan jadwal, lokasi, kelas, biaya, kuota terisi, dan hadiah utama tanpa membocorkan data pribadi.
- Peserta dapat melengkapi profil pribadi yang terpisah dari informasi publik.
- Peserta dapat membuat, melihat, dan mengubah data burung miliknya.
- Foto burung dibatasi ke JPG, JPEG, PNG, atau WebP dengan ukuran maksimal 5 MB dan nama file yang dibuat sistem.
- Peserta dapat memilih kelas dan burung yang sesuai untuk membuat pendaftaran.
- Biaya kelas disalin ke `registration_fee_snapshot` saat pendaftaran agar riwayat tidak berubah ketika harga kelas diubah.
- Nomor pendaftaran dibuat otomatis dalam format `REG-TAHUN-NOMOR`.
- Pendaftaran baru dimulai dengan status `PENDING_PAYMENT`.
- Admin event dapat melihat daftar dan detail pendaftaran pada event yang ditugaskan.
- Aksi profil, burung, dan pendaftaran penting dicatat pada `activity_logs`.

## Integritas kuota dan aturan pendaftaran

- Pengecekan kuota dan pembuatan pendaftaran berjalan dalam satu transaksi database.
- Baris kelas dikunci saat pendaftaran agar dua permintaan bersamaan tidak melewati kuota.
- Pendaftaran hanya tersedia untuk event `UPCOMING` yang dipublikasikan dan masih berada dalam periode pendaftaran.
- Jenis burung harus sama dengan jenis burung kelas.
- Burung harus dimiliki oleh peserta yang sedang masuk.
- Burung yang sama tidak dapat didaftarkan dua kali pada kelas yang sama.
- Schema tetap mengizinkan burung yang sama mengikuti kelas berbeda karena aturan bisnis ini belum diputuskan.
- Untuk sementara, semua pendaftaran selain `CANCELLED` memakai satu slot. Kebijakan kedaluwarsa atau pelepasan slot untuk pembayaran yang tertunda belum ditetapkan.

## Batas keamanan dan privasi

- Peserta tidak dapat membuka atau mengubah burung milik peserta lain.
- Peserta tidak dapat melihat pendaftaran peserta lain.
- Admin yang tidak ditugaskan pada event tidak dapat melihat pendaftarannya.
- Detail pendaftaran dari event lain ditolak meskipun ID pendaftaran valid.
- Email, telepon, alamat, bukti pembayaran, dan data audit tidak ditampilkan pada halaman publik.

## Belum termasuk

- Upload dan riwayat bukti pembayaran.
- Verifikasi atau penolakan pembayaran oleh Admin.
- Bukti pendaftaran PDF.
- Penugasan juri, penjurian, pelanggaran/DQ, TOPSIS, realtime, dan sertifikat.
- Pembatalan peserta dan kebijakan waktu kedaluwarsa pembayaran.

## Skenario review

1. Login sebagai Owner, buat event dan publikasikan event yang memiliki kelas.
2. Buka halaman publik **Agenda** dan periksa detail event serta ketersediaan kelas.
3. Login sebagai Peserta, lengkapi **Profil Saya**, lalu tambahkan burung yang jenisnya sesuai kelas.
4. Buka **Agenda Event**, pilih event dan kelas, lalu konfirmasi pendaftaran.
5. Periksa nomor pendaftaran, status menunggu pembayaran, dan biaya yang telah disalin.
6. Login sebagai Admin yang ditugaskan, buka event, lalu periksa menu **Peserta terdaftar**.
7. Coba akses sebagai Admin yang tidak ditugaskan untuk memastikan akses ditolak.

## Tahap berikutnya

**Tahap 4 — Payment & Verification**: upload bukti pembayaran privat, riwayat percobaan pembayaran, verifikasi atau penolakan oleh Admin, sinkronisasi status pendaftaran secara transaksional, dan bukti pendaftaran PDF untuk peserta terverifikasi.
