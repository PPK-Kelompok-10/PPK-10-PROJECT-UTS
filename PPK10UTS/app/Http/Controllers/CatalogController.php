<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Reservation;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CatalogController extends Controller
{
    /**
     * US 1 & 2: daftar fasilitas + pencarian/filter tipe, lokasi, kapasitas.
     * Bisa diakses Pengunjung (tanpa login) maupun Pengguna.
     */
    public function index(Request $request)
    {
        $facilities = Facility::query()
            ->active()
            ->when($request->filled('type'), fn ($q) => $q->where('type', $request->type))
            ->when($request->filled('location'), fn ($q) => $q->where('location', 'like', '%' . $request->location . '%'))
            ->when($request->filled('min_capacity'), fn ($q) => $q->where('capacity', '>=', (int) $request->min_capacity))
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        $types = Facility::active()->distinct()->pluck('type');

        return view('catalog.index', compact('facilities', 'types'));
    }

    /**
     * US 1: detail fasilitas + ketersediaan per slot waktu untuk tanggal tertentu.
     * Pengunjung hanya melihat status tersedia/tidak tersedia, TANPA detail pemohon/tujuan
     * (sesuai ketentuan di dokumen tugas).
     */
    public function show(Request $request, Facility $facility)
    {
        $date = $request->filled('date')
            ? Carbon::parse($request->date)
            : Carbon::today();

        $bookedRanges = Reservation::active()
            ->where('facility_id', $facility->id)
            ->whereDate('start_time', $date->toDateString())
            ->get(['start_time', 'end_time']);

        $slots = $this->buildDaySlots($date, $bookedRanges);

        // Cegah browser menampilkan halaman lama dari cache (misal lewat tombol Back)
        // setelah reservasi dibatalkan — pastikan ketersediaan slot selalu data terbaru.
        return response()
            ->view('catalog.show', compact('facility', 'date', 'slots'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    /**
     * Bangun daftar slot 30 menit dari 07.00-20.00, tandai mana yang sudah terpakai.
     */
    private function buildDaySlots(Carbon $date, $bookedRanges): array
    {
        $slots = [];
        $cursor = $date->copy()->setTime(7, 0);
        $end = $date->copy()->setTime(20, 0);

        while ($cursor < $end) {
            $slotEnd = $cursor->copy()->addMinutes(30);

            $isBooked = $bookedRanges->contains(function ($reservation) use ($cursor, $slotEnd) {
                return $cursor < $reservation->end_time && $slotEnd > $reservation->start_time;
            });

            $slots[] = [
                'start' => $cursor->format('H:i'),
                'end' => $slotEnd->format('H:i'),
                'available' => !$isBooked,
            ];

            $cursor = $slotEnd;
        }

        return $slots;
    }
}
