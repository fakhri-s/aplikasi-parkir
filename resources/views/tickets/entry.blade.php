{{--
    Catatan pembelajaran:
    Template karcis masuk ini menampilkan informasi kendaraan saat masuk ke parkir, seperti nomor karcis, plat, jenis kendaraan, area, dan waktu masuk.
    Struktur dasar view: menerima data, menampilkan HTML, lalu menyisipkan interaksi JavaScript jika diperlukan.
--}}

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <title>Karcis Masuk</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
            background: #f8fafc;
            color: #0f172a;
        }

        .ticket {
            width: 180px;
            margin: 18px auto;
            padding: 18px 14px;
            border: 2px solid #0f172a;
            border-radius: 12px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            box-shadow: 0 10px 20px rgba(15, 23, 42, 0.08);
            text-align: center;
        }

        .brand {
            font-size: 16px;
            font-weight: bold;
            letter-spacing: 2px;
            margin-bottom: 10px;
        }

        .label {
            font-size: 9px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1.3px;
        }

        .value {
            font-size: 13px;
            font-weight: bold;
            margin: 4px 0 10px;
        }

        .barcode {
            margin: 12px auto 8px;
            width: 100%;
            text-align: center;
        }

        .barcode img {
            display: block;
            width: 165px;
            height: 50px;
            margin: 0 auto;
        }

        .divider {
            border-top: 1px dashed #cbd5e1;
            margin: 12px 0;
        }

        .small {
            font-size: 9px;
            color: #475569;
            line-height: 1.6;
        }
    </style>
</head>

<body>
    <div class="ticket">
        <div class="brand">PARKING</div>
        <div class="label">Karcis Masuk</div>
        <div class="barcode"> <img src="{{ $barcode }}" alt="Barcode"></div>
        <div class="small">{{ $nomor_karcis }}</div>

        <div class="label">Plat Nomor</div>
        <div class="value">{{ $plat_nomor }}</div>

        <div class="label">Jenis Kendaraan</div>
        <div class="value">{{ $jenis_kendaraan }}</div>

        <div class="label">Area</div>
        <div class="value">{{ $area_nama }}</div>

        <div class="label">Waktu Masuk</div>
        <div class="value">{{ $waktu_masuk }}</div>

        <div class="divider"></div>
        <div class="small">Tunjukkan karcis ini saat keluar dari area parkir.</div>
    </div>
</body>

</html>