<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Bukti Pendaftaran {{ $registration->registration_number }}</title>
    <style>
        @page { margin: 42px; }
        body { font-family: "DejaVu Sans", sans-serif; color: #26352f; font-size: 11px; line-height: 1.6; }
        .header { background: #173a31; color: white; padding: 26px; border-bottom: 5px solid #d9a441; }
        .brand { color: #ead29e; font-size: 11px; letter-spacing: 2px; }
        h1 { font-size: 25px; line-height: 1.25; margin: 10px 0; }
        .number { font-size: 12px; color: #dce9e2; }
        .section { margin-top: 24px; page-break-inside: avoid; }
        .eyebrow { color: #276453; font-weight: bold; font-size: 10px; letter-spacing: 1px; text-transform: uppercase; }
        h2 { margin: 5px 0 14px; font-size: 19px; line-height: 1.4; }
        table { border-collapse: collapse; width: 100%; table-layout: fixed; }
        td { padding: 11px 12px; border-bottom: 1px solid #e6e8e5; vertical-align: top; overflow-wrap: break-word; }
        td.label { width: 30%; color: #66736b; background: #f4f6f3; }
        .verified { margin-top: 24px; padding: 16px 20px; border: 1px solid #a5c3b4; background: #eef6f0; color: #173a31; }
        .verified strong { font-size: 14px; }
        .footer { margin-top: 28px; padding-top: 14px; border-top: 1px solid #e6e8e5; color: #66736b; font-size: 10px; }
    </style>
</head>
<body>
    <div class="header">
        <div class="brand">GANTANGSCORE / SIPENGANTANG</div>
        <h1>Bukti Pendaftaran</h1>
        <div class="number">{{ $registration->registration_number }}</div>
    </div>
    <div class="section">
        <div class="eyebrow">Kompetisi burung berkicau</div>
        <h2>{{ $registration->competitionClass->event->name }}</h2>
        <table>
            <tr><td class="label">Peserta</td><td>{{ $registration->participant->name }}</td></tr>
            <tr><td class="label">Nama burung</td><td>{{ $registration->bird->name }}</td></tr>
            <tr><td class="label">Jenis burung</td><td>{{ $registration->bird->bird_type }}</td></tr>
            <tr><td class="label">Kelas kompetisi</td><td>{{ $registration->competitionClass->name }}</td></tr>
            <tr><td class="label">Nomor gantangan</td><td>{{ $registration->gantangan_number ?? 'Belum dipilih peserta' }}</td></tr>
            <tr><td class="label">Tanggal event</td><td>{{ $registration->competitionClass->event->start_at->translatedFormat('d F Y, H.i') }} WIB</td></tr>
            <tr><td class="label">Lokasi</td><td>{{ $registration->competitionClass->event->location_name }}<br>{{ $registration->competitionClass->event->address }}</td></tr>
            <tr><td class="label">Biaya pendaftaran</td><td>Rp{{ number_format($registration->registration_fee_snapshot, 0, ',', '.') }}</td></tr>
        </table>
    </div>
    <div class="verified">
        <strong>VERIFIED - Pembayaran terverifikasi</strong><br>
        Dikonfirmasi panitia pada {{ $registration->verified_at?->translatedFormat('d F Y, H.i') ?? '-' }} WIB.
    </div>
    <div class="footer">
        Simpan bukti ini dan tunjukkan kepada panitia saat registrasi ulang.
        Nomor gantangan yang belum tersedia akan diinformasikan oleh panitia.
    </div>
</body>
</html>
