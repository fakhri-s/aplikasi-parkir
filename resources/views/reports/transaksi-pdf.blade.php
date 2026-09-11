{{--
    Catatan pembelajaran:
    Template PDF laporan transaksi ini dirancang untuk cetak. Layout dibuat ringkas agar mudah dibaca saat dicetak ke PDF.
    Struktur dasar view: menerima data, menampilkan HTML, lalu menyisipkan interaksi JavaScript jika diperlukan.
--}}

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Laporan Transaksi</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        .header {
            margin-bottom: 18px;
            border-bottom: 2px solid #1f2937;
            padding-bottom: 8px;
        }

        .title {
            font-size: 20px;
            font-weight: bold;
            margin: 0;
        }

        .meta {
            margin-top: 8px;
            color: #475569;
        }

        .summary {
            margin-bottom: 18px;
        }

        .summary table {
            width: 100%;
            border-collapse: collapse;
        }

        .summary td {
            border: 1px solid #cbd5e1;
            padding: 8px 10px;
            background: #f8fafc;
        }

        .summary .label {
            width: 40%;
            font-weight: bold;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            border: 1px solid #cbd5e1;
            padding: 6px 6px;
            text-align: left;
            vertical-align: top;
        }

        th {
            background: #e2e8f0;
            font-size: 10px;
            text-transform: uppercase;
        }

        .muted {
            color: #64748b;
        }
    </style>
</head>
<body>
    <div class="header">
        <p class="title">Laporan Transaksi Parkir</p>
        <div class="meta">Periode: {{ $startDate }} s.d {{ $endDate }}</div>
    </div>

    <div class="summary">
        <table>
            <tr>
                <td class="label">Jumlah Transaksi</td>
                <td>{{ $totals['count'] }}</td>
            </tr>
            <tr>
                <td class="label">Pendapatan</td>
                <td>Rp {{ number_format($totals['income'], 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td class="label">Masuk</td>
                <td>{{ $totals['active'] }}</td>
            </tr>
            <tr>
                <td class="label">Keluar</td>
                <td>{{ $totals['closed'] }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Plat</th>
                <th>Nomor Karcis</th>
                <th>Jenis Pelanggan</th>
                <th>Area</th>
                <th>Masuk</th>
                <th>Keluar</th>
                <th>Durasi</th>
                <th>Total</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($transactions as $index => $transaction)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $transaction->plat_nomor ?? '-' }}</td>
                    <td>{{ $transaction->nomor_karcis ?? '-' }}</td>
                    <td>{{ $transaction->jenisPelanggan?->nama ?? 'Reguler' }}</td>
                    <td>{{ $transaction->areaParkir?->nama ?? '-' }}</td>
                    <td>{{ $transaction->waktu_masuk?->format('d M Y H:i') ?? '-' }}</td>
                    <td>{{ $transaction->waktu_keluar?->format('d M Y H:i') ?? '-' }}</td>
                    <td>{{ $transaction->durasi !== null ? $transaction->durasi . ' menit' : '-' }}</td>
                    <td>Rp {{ number_format((int) ($transaction->total_bayar ?? 0), 0, ',', '.') }}</td>
                    <td>{{ $transaction->status === 'keluar' ? 'Keluar' : 'Masuk' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="muted">Tidak ada data transaksi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>