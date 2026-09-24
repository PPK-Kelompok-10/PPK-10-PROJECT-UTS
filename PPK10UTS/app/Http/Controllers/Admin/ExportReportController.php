<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\RecapFilterRequest;
use App\Services\FacilityRecapService;
use Barryvdh\DomPDF\Facade\Pdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportReportController extends Controller
{
    /**
     * Pemisah kolom CSV. Kalau di Excel semua data masuk ke satu kolom
     * (biasa terjadi di Windows dengan pengaturan wilayah Indonesia), ganti menjadi ';'.
     */
    private const CSV_DELIMITER = ',';

    public function __construct(private FacilityRecapService $recap)
    {
    }

    /**
     * US 17: halaman rekap okupansi & frekuensi kerusakan per fasilitas.
     */
    public function index(RecapFilterRequest $request)
    {
        return view('admin.exports.index', $this->recap->build($request->from(), $request->to()));
    }

    /**
     * US 17: unduh rekap dalam format CSV (bisa dibuka di Excel / Google Sheets).
     */
    public function csv(RecapFilterRequest $request): StreamedResponse
    {
        $recap = $this->recap->build($request->from(), $request->to());

        return response()->streamDownload(function () use ($recap) {
            $out = fopen('php://output', 'w');

            // BOM supaya Excel membaca file sebagai UTF-8.
            fwrite($out, "\xEF\xBB\xBF");

            $this->writeCsvRow($out, [
                'No', 'Fasilitas', 'Tipe', 'Lokasi', 'Status',
                'Reservasi Disetujui', 'Jam Terpakai', 'Okupansi (%)',
                'Laporan Kerusakan', 'Laporan Selesai', 'Laporan Terbuka', 'Kategori Terbanyak',
            ]);

            foreach ($recap['rows'] as $row) {
                $this->writeCsvRow($out, [
                    $row['no'], $row['name'], $row['type'], $row['location'], $row['status_label'],
                    $row['approved_reservations'], $row['booked_hours'], $row['occupancy_percent'],
                    $row['reports_total'], $row['reports_resolved'], $row['reports_open'], $row['top_category'],
                ]);
            }

            fclose($out);
        }, $this->filename($recap, 'csv'), [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /**
     * US 17: unduh rekap dalam format PDF (butuh barryvdh/laravel-dompdf).
     */
    public function pdf(RecapFilterRequest $request)
    {
        $recap = $this->recap->build($request->from(), $request->to());

        return Pdf::loadView('admin.exports.pdf', $recap + ['generatedAt' => now()])
            ->setPaper('a4', 'landscape')
            ->download($this->filename($recap, 'pdf'));
    }

    private function filename(array $recap, string $extension): string
    {
        return sprintf(
            'rekap-fasilitas_%s_%s.%s',
            $recap['from']->format('Ymd'),
            $recap['to']->format('Ymd'),
            $extension
        );
    }

    /**
     * @param  resource  $handle
     */
    private function writeCsvRow($handle, array $row): void
    {
        // Parameter escape diisi eksplisit supaya tidak muncul deprecation di PHP 8.4+.
        fputcsv($handle, array_map($this->sanitizeCsvValue(...), $row), self::CSV_DELIMITER, '"', '');
    }

    /**
     * Cegah CSV injection: teks yang diawali =, +, -, @ bisa dieksekusi Excel sebagai formula.
     */
    private function sanitizeCsvValue(mixed $value): mixed
    {
        if (is_string($value) && $value !== '' && in_array($value[0], ['=', '+', '-', '@', "\t", "\r"], true)) {
            return "'" . $value;
        }

        return $value;
    }
}
