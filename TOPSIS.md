# TOPSIS — Kontrak Implementasi Tahap 7

Algoritme: `TOPSIS`, versi `1.0.0`. Ruang lingkup selalu satu kelas.

## Sumber dan perhitungan

`RankingInputBuilder` hanya memakai pendaftaran `VERIFIED` dan evaluasi `SUBMITTED` lengkap dari penugasan Juri aktif. Draft, Juri yang diganti, peserta belum terverifikasi, batal, dan DQ tidak masuk input. Nilai submitted tidak lengkap/invalid menyebabkan kegagalan validasi, bukan dianggap nol. Rata-rata setiap kriteria adalah jumlah nilai submitted dibagi jumlah evaluasi valid; nilai antara tidak dibulatkan.

`TopsisCalculator` tidak mengakses database. Ia memvalidasi kode kriteria unik, bobot positif berjumlah 1 pada toleransi presisi bobot 6 desimal, tipe BENEFIT untuk MVP, serta rata-rata 1–10. Rumus: normalisasi vektor per kolom; kalikan bobot; ideal positif maksimum dan negatif minimum; jarak Euclidean; preferensi `D- / (D+ + D-)`. Semakin besar semakin baik.

Tidak ada alternatif → tidak ada pemenang, finalisasi ditolak. Satu alternatif → rank 1, preferensi netral 0,5, bukan bukti menang komparatif. Semua alternatif identik → preferensi 0,5 dan draw.

## Presisi dan urutan

Perhitungan memakai presisi numerik PHP tanpa pembulatan antara. Jarak dan preferensi disimpan sebagai DECIMAL(16,10); UI menampilkan preferensi 4 desimal. Koefisien yang sama pada 10 desimal membentuk kelompok draw deterministik. Angka sama di tampilan 4 desimal belum tentu draw pada presisi penyimpanan. Urutan ID hanya menstabilkan tampilan kelompok seri, tidak menentukan pemenang. Shared competition rank awal: 1,1,3.

## Keputusan Owner: lempar dadu nyata

Pada 21 Oktober 2026 Owner menyetujui minimum 3 Juri, pengganti Juri absen, serta penyelesaian seri lewat lempar dadu nyata. Total dadu tertinggi menang; Admin mencatat urutan hasilnya pada **tepat seluruh anggota kelompok seri**. Sistem tidak menebak jumlah/sisi dadu, tidak mengocok dadu, dan tidak memilih pemenang otomatis. Catatan kejadian/total dadu dan konfirmasi wajib. Jika lemparan masih seri, tunggu hasil undian yang benar-benar menentukan urutan sebelum mencatatnya.

Koefisien/matriks/rank matematis asli tetap di snapshot provisional. `ranking_tie_resolutions` mencatat urutan, petugas, waktu, dan catatan secara append-only. FINAL menyimpan koefisien asli, `is_tied=true`, serta urutan resmi hasil undian dan salinan bukti keputusan. Peserta non-seri tidak dapat dipindahkan melalui formulir draw.

Keputusan terikat pada hash sumber (versi, kriteria, Juri, peserta, nilai submitted, identitas publik, hadiah). Sumber berubah → keputusan lama tidak dipakai untuk finalisasi. Hasil lama tetap menjadi riwayat. Keputusan yang sudah disimpan tidak dapat ditimpa; koreksi administratif keputusan yang salah memerlukan alur revisi ber-audit terpisah, bukan edit diam-diam.

## Provisional dan final

Provisional boleh memakai evaluasi submitted yang tersedia, belum resmi, dan hanya untuk petugas. Event `JudgeScoreSubmitted`/`ParticipantDisqualified` setelah commit memicu job `RecalculateProvisionalRanking`. Job unique-until-processing per kelas, transaksi lock kelas, hash idempoten, dan no-op setelah finalisasi. Database queue cukup; Redis/Reverb tidak wajib. Admin juga dapat menghitung ulang via HTTP bila worker belum berjalan.

Finalisasi membutuhkan minimal 3 Juri aktif, setidaknya satu peserta eligible, seluruh penugasan aktif selesai menilai seluruh peserta eligible, bobot/struktur valid, dan seluruh draw terselesaikan. Aksi mengunci kelas, menghitung dari raw source lagi, menulis snapshot FINAL/results dan audit, mengunci kelas, lalu event setelah commit. Tidak boleh menyalin provisional menjadi final tanpa menghitung ulang. Sertifikat/realtime ditangani Tahap 8 melalui `ResultFinalized`.

Hasil resmi publik membaca snapshot FINAL tersimpan, bukan data identitas/hadiah live dan bukan menghitung ulang TOPSIS. Raw score Juri, matriks, catatan undian, dan kontak privat tidak diberikan kepada publik.

## Golden fixture independen

Dua kriteria BENEFIT dengan bobot 0,6 / 0,4. Alternatif A=(3,4), B=(4,3). Kedua kolom bernorma 5. Normalisasi A=(0,6;0,8), B=(0,8;0,6). Terbobot A=(0,36;0,32), B=(0,48;0,24). Ideal+=(0,48;0,32), ideal-=(0,36;0,24). A: D+=0,12; D-=0,08; V=0,4. B: D+=0,08; D-=0,12; V=0,6. B rank 1, A rank 2. Mesin matematika diuji dengan fixture kecil ini; builder produksi tetap mewajibkan lima kriteria snapshot MVP.

## Integrasi Tahap 8

Rumus, presisi, dan versi algoritme tetap 1.0.0. Input snapshot baru menambahkan `class.event_start_at` serta `class.organizer_name` untuk piagam. Karena metadata itu ikut hash sumber, provisional/keputusan draw dari versi input lama perlu dihitung dan ditinjau ulang sebelum finalisasi; keputusan lama tetap menjadi riwayat. FINAL lama tidak dihitung ulang atau diubah. Piagam membaca posisi hadiah dan rank resmi dari FINAL, termasuk urutan hasil dadu yang dicatat Admin. Detail kompatibilitas tanggal FINAL lama dijelaskan pada kontrak Tahap 8.
