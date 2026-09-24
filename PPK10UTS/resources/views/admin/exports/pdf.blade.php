<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Rekap Fasilitas {{ $from->format('d/m/Y') }} - {{ $to->format('d/m/Y') }}</title>
    <style>
        * { font-family: "DejaVu Sans", sans-serif; }
        body { font-size: 10px; color: #1f2937; margin: 0; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        .meta { color: #6b7280; margin-bottom: 12px; }
        .summary { margin-bottom: 12px; border-collapse: collapse; }
        .summary td { padding: 2px 16px 2px 0; }
        .summary strong { font-size: 12px; }
        table.data { width: 100%; border-collapse: collapse; }
        table.data th, table.data td { border: 1px solid #d1d5db; padding: 4px 6px; vertical-align: top; }
        table.data th { background: #f3f4f6; text-align: left; }
        .num { text-align: right; }
        .muted { color: #6b7280; font-size: 9px; }
        .footer { margin-top: 12px; color: #6b7280; font-size: 9px; }
    </style>
</head>
<body>
    <h1>Rekap Okupansi &amp; Kerusakan Fasilitas</h1>
    <div class="meta">
        Periode {{ $from->format('d/m/Y') }} sampai {{ $to->format('d/m/Y') }} ({{ $days }} hari).
        Dibuat {{ $generatedAt->format('d/m/Y H:i') }}.
    </div>

    <table class="summary">
        <tr>
            <td>Reservasi disetujui<br><strong>{{ $summary['approved_reservations'] }}</strong></td>
            <td>Total jam terpakai<br><strong>{{ $summary['booked_hours'] }}</strong></td>
            <td>Rata-rata okupansi<br><strong>{{ $summary['average_occupancy'] }}%</strong></td>
            <td>Laporan kerusakan<br><strong>{{ $summary['reports_total'] }}</strong></td>
            <td>Laporan terbuka<br><strong>{{ $summary['reports_open'] }}</strong></td>
        </tr>
    </table>

    <table class="data">
        <thead>
            <tr>
                <th class="num">No</th>
                <th>Fasilitas</th>
                <th>Status</th>
                <th class="num">Reservasi disetujui</th>
                <th class="num">Jam terpakai</th>
                <th class="num">Okupansi</th>
                <th class="num">Laporan</th>
                <th class="num">Selesai</th>
                <th class="num">Terbuka</th>
                <th>Kategori terbanyak</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="num">{{ $row['no'] }}</td>
                    <td>
                        {{ $row['name'] }}<br>
                        <span class="muted">{{ $row['type'] }}, {{ $row['location'] }}</span>
                    </td>
                    <td>{{ $row['status_label'] }}</td>
                    <td class="num">{{ $row['approved_reservations'] }}</td>
                    <td class="num">{{ $row['booked_hours'] }}</td>
                    <td class="num">{{ $row['occupancy_percent'] }}%</td>
                    <td class="num">{{ $row['reports_total'] }}</td>
                    <td class="num">{{ $row['reports_resolved'] }}</td>
                    <td class="num">{{ $row['reports_open'] }}</td>
                    <td>{{ $row['top_category'] }}</td>
                </tr>
            @empty
                <tr><td colspan="10">Belum ada data fasilitas.</td></tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
