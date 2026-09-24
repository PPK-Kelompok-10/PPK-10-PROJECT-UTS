<?php

namespace App\Services;

use App\Models\Facility;
use App\Models\Report;
use App\Models\Reservation;
use Carbon\CarbonInterface;

/**
 * US 17: menghitung rekap okupansi dan frekuensi kerusakan per fasilitas.
 * Dipakai bersama oleh halaman rekap, ekspor CSV, dan ekspor PDF supaya angkanya selalu sama.
 *
 * Definisi:
 * - Okupansi   = total jam reservasi berstatus 'approved' / (jumlah hari x 13 jam operasional) x 100%.
 *                Reservasi pending, rejected, dan cancelled tidak dihitung.
 * - Kerusakan  = jumlah laporan yang DIBUAT dalam periode (semua status).
 */
class FacilityRecapService
{
    /** Jam operasional 07.00-20.00, sama dengan ketentuan slot reservasi. */
    public const OPERATIONAL_HOURS_PER_DAY = 13;

    private const FACILITY_STATUS_LABELS = [
        'aktif' => 'Aktif',
        'dalam_perbaikan' => 'Dalam perbaikan',
        'nonaktif' => 'Nonaktif',
    ];

    public function build(CarbonInterface $from, CarbonInterface $to): array
    {
        $from = $from->copy()->startOfDay();
        $to = $to->copy()->endOfDay();

        $days = (int) $from->diffInDays($to->copy()->startOfDay()) + 1;
        $capacityHours = $days * self::OPERATIONAL_HOURS_PER_DAY;

        // Durasi dihitung di PHP (bukan SQL) supaya jalan sama di MySQL maupun SQLite.
        $reservationsByFacility = Reservation::query()
            ->where('status', 'approved')
            ->whereBetween('start_time', [$from, $to])
            ->get(['facility_id', 'start_time', 'end_time'])
            ->groupBy('facility_id');

        $reportsByFacility = Report::query()
            ->whereBetween('created_at', [$from, $to])
            ->get(['facility_id', 'category', 'status'])
            ->groupBy('facility_id');

        $rows = Facility::query()
            ->orderBy('name')
            ->get()
            ->values()
            ->map(function (Facility $facility, int $index) use ($reservationsByFacility, $reportsByFacility, $capacityHours) {
                $reservations = $reservationsByFacility->get($facility->id, collect());
                $reports = $reportsByFacility->get($facility->id, collect());

                $bookedHours = $reservations->sum(
                    fn ($reservation) => abs($reservation->start_time->diffInMinutes($reservation->end_time)) / 60
                );

                $topCategory = $reports->countBy('category')->sortDesc()->keys()->first();

                return [
                    'no' => $index + 1,
                    'name' => $facility->name,
                    'type' => $facility->type,
                    'location' => $facility->location,
                    'status' => $facility->status,
                    'status_label' => self::FACILITY_STATUS_LABELS[$facility->status] ?? $facility->status,
                    'approved_reservations' => $reservations->count(),
                    'booked_hours' => round($bookedHours, 1),
                    'occupancy_percent' => $capacityHours > 0 ? round($bookedHours / $capacityHours * 100, 1) : 0.0,
                    'reports_total' => $reports->count(),
                    'reports_resolved' => $reports->where('status', 'selesai')->count(),
                    'reports_open' => $reports->whereIn('status', Report::OPEN_STATUSES)->count(),
                    'top_category' => $topCategory ? (Report::CATEGORIES[$topCategory] ?? $topCategory) : '-',
                ];
            });

        $summary = [
            'facilities' => $rows->count(),
            'approved_reservations' => $rows->sum('approved_reservations'),
            'booked_hours' => round($rows->sum('booked_hours'), 1),
            'average_occupancy' => $rows->isNotEmpty() ? round($rows->avg('occupancy_percent'), 1) : 0.0,
            'reports_total' => $rows->sum('reports_total'),
            'reports_open' => $rows->sum('reports_open'),
        ];

        return compact('from', 'to', 'days', 'capacityHours', 'rows', 'summary');
    }
}
