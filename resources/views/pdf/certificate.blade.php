<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Piagam {{ $certificate['number'] }}</title>
    <style>
        @page { margin: 26px; }
        body { font-family: "DejaVu Sans", sans-serif; margin: 0; color: #173A31; font-size: 12px; }
        .frame { border: 3px solid #276453; padding: 12px; }
        .paper { border: 1px solid #D9A441; padding: 25px 42px 20px; text-align: center; }
        .brand { font-size: 14px; font-weight: bold; letter-spacing: 3px; }
        .eyebrow { margin-top: 7px; font-size: 9px; letter-spacing: 2px; color: #5c6c64; }
        .rule { border-top: 2px solid #D9A441; width: 110px; margin: 18px auto; }
        h1 { font-size: 32px; margin: 0; letter-spacing: 3px; }
        .number { color: #69776f; font-size: 9px; margin: 7px 0 18px; }
        .small { color: #5c6c64; margin: 8px 0; }
        .recipient { font-size: {{ mb_strlen($certificate['participant_name']) > 65 ? 17 : 25 }}px; font-weight: bold; margin: 9px 0; line-height: 1.3; word-wrap: break-word; }
        .bird { font-size: {{ mb_strlen($certificate['bird_name']) > 65 ? 14 : 19 }}px; font-weight: bold; margin: 5px 0 2px; line-height: 1.3; word-wrap: break-word; }
        .rank { color: #8a601c; font-size: 28px; font-weight: bold; margin: 13px 0 8px; letter-spacing: 2px; }
        .class { font-size: {{ mb_strlen($certificate['class_name']) > 75 ? 12 : 16 }}px; font-weight: bold; margin: 4px 0; line-height: 1.3; word-wrap: break-word; }
        .event { font-size: {{ mb_strlen($certificate['event_name']) > 75 ? 11 : 14 }}px; margin: 5px 0; line-height: 1.3; word-wrap: break-word; }
        .footer { margin-top: 19px; padding-top: 12px; border-top: 1px solid #d7ddd8; font-size: 10px; line-height: 1.6; }
        .notice { color: #69776f; font-size: 8px; margin-top: 8px; }
    </style>
</head>
<body>
    <div class="frame"><div class="paper">
        <div class="brand">{{ $certificate['organizer_name'] }}</div>
        <div class="eyebrow">KOMPETISI BURUNG BERKICAU</div>
        <div class="rule"></div>
        <h1>PIAGAM PENGHARGAAN</h1>
        <div class="number">Nomor {{ $certificate['number'] }}</div>
        <p class="small">Diberikan kepada</p>
        <div class="recipient">{{ $certificate['participant_name'] }}</div>
        <p class="small">bersama burung</p>
        <div class="bird">{{ $certificate['bird_name'] }}</div>
        <div class="small">{{ $certificate['bird_type'] }}</div>
        <div class="rank">JUARA {{ $certificate['rank'] }}</div>
        <div class="class">Kelas {{ $certificate['class_name'] }}</div>
        <div class="event">{{ $certificate['event_name'] }}</div>
        <div class="footer">{{ $certificate['event_date'] }}<br>Penyelenggara: <strong>{{ $certificate['organizer_name'] }}</strong></div>
        <div class="notice">Diterbitkan berdasarkan snapshot hasil FINAL. Nomor piagam tercatat pada sistem penyelenggara.</div>
    </div></div>
</body>
</html>
