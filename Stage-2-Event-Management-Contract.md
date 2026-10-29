# Tahap 2 — Event Management

Status: **selesai dan siap direview**

## Ruang lingkup yang aktif

- Owner membuat, melihat, mengubah, mengunggah banner, dan mempublikasikan event.
- Event baru selalu dimulai sebagai `DRAFT`; publikasi memakai aksi khusus.
- Owner dapat menugaskan akun Admin yang sudah ada.
- Owner dapat membuat akun Admin baru sekaligus menugaskannya ke event.
- Admin hanya dapat melihat dan mengelola event yang tercatat pada `event_admins`.
- Admin mengelola kelas kompetisi, biaya Rupiah, kuota, dan jadwal.
- Pembuatan kelas otomatis menyimpan snapshot lima kriteria penilaian berikut:
  - C1 Irama Lagu — 40%
  - C2 Durasi Kerja / Kestabilan — 20%
  - C3 Volume — 20%
  - C4 Gaya — 10%
  - C5 Fisik — 10%
- Admin menambahkan dan menghapus hadiah per peringkat.
- Aksi penting tersimpan pada `activity_logs`.

## Batas keamanan

- Peran global `ADMIN` tidak memberi akses ke semua event.
- Owner tidak dapat membuka event milik Owner lain.
- Resource kelas dan hadiah diverifikasi terhadap rantai event induknya.
- Perubahan status publikasi tidak menggunakan input status generik.

## Belum termasuk

- Profil peserta dan data burung.
- Pendaftaran peserta ke kelas.
- Snapshot biaya pada pendaftaran.
- Upload serta verifikasi bukti pembayaran.
- Penjurian, pelanggaran/DQ, TOPSIS, realtime, dan sertifikat.

## Skenario review

1. Login sebagai Owner, buka menu **Event**, lalu buat event draft.
2. Buka detail event dan tetapkan Admin yang ada atau buat Admin baru.
3. Login sebagai Admin tersebut dan buka event yang ditugaskan.
4. Tambahkan kelas, lalu periksa lima kriteria snapshot.
5. Tambahkan hadiah uang atau barang pada kelas.
6. Kembali sebagai Owner dan publikasikan event draft.

## Tahap berikutnya

**Tahap 3 — Participant & Registration**: profil peserta, data burung, detail event publik, pendaftaran ke kelas, pengecekan kuota secara transaksional, dan snapshot biaya pendaftaran.
