<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateReportStatusRequest;
use App\Models\Report;
use App\Models\Reservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportResolutionController extends Controller
{
    private const FILTERS = [
        'terbuka' => 'Perlu ditangani',
        'baru' => 'Baru',
        'diproses' => 'Diproses',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
        'semua' => 'Semua',
    ];

    /**
     * US 11: daftar laporan untuk Petugas. Default hanya yang masih terbuka,
     * diurutkan dari yang paling lama menunggu (antrean).
     */
    public function index(Request $request)
    {
        $filter = $request->string('status')->toString();
        $filter = array_key_exists($filter, self::FILTERS) ? $filter : 'terbuka';

        $category = $request->string('category')->toString();
        $category = array_key_exists($category, Report::CATEGORIES) ? $category : null;

        $query = Report::with(['facility', 'user'])
            ->when($filter === 'terbuka', fn ($q) => $q->open())
            ->when(array_key_exists($filter, Report::STATUSES), fn ($q) => $q->where('status', $filter))
            ->when($category, fn ($q) => $q->where('category', $category));

        in_array($filter, ['terbuka', 'baru', 'diproses'], true)
            ? $query->oldest()
            : $query->latest();

        $reports = $query->paginate(15)->withQueryString();

        return view('petugas.reports.index', [
            'reports' => $reports,
            'filter' => $filter,
            'filters' => self::FILTERS,
            'filterCounts' => $this->filterCounts(),
            'category' => $category,
            'categories' => Report::CATEGORIES,
        ]);
    }

    /**
     * US 11 & 12: detail laporan, form ubah status, dan aksi status fasilitas.
     */
    public function show(Report $report)
    {
        $report->load(['facility', 'user', 'handler']);

        // Info pendukung sebelum menandai fasilitas "dalam perbaikan".
        $upcomingReservations = Reservation::active()
            ->where('facility_id', $report->facility_id)
            ->where('start_time', '>=', now())
            ->count();

        $otherOpenReports = Report::open()
            ->where('facility_id', $report->facility_id)
            ->whereKeyNot($report->id)
            ->count();

        return view('petugas.reports.show', compact('report', 'upcomingReservations', 'otherOpenReports'));
    }

    /**
     * US 11: perbarui status laporan + catatan resolusi.
     */
    public function update(UpdateReportStatusRequest $request, Report $report)
    {
        $status = $request->validated('status');

        $report->fill([
            'status' => $status,
            'handled_by' => $request->user()->id,
            'resolved_at' => in_array($status, Report::CLOSED_STATUSES, true) ? now() : null,
        ]);

        if ($request->filled('resolution_note')) {
            $report->resolution_note = $request->validated('resolution_note');
        }

        $report->save();

        return redirect()->route('petugas.reports.show', $report)
            ->with('status', "Status laporan diperbarui menjadi \"{$report->statusLabel()}\".");
    }

    /**
     * US 12: tandai fasilitas "dalam perbaikan" berdasarkan laporan yang sedang ditangani.
     * Fasilitas otomatis hilang dari katalog dan tidak bisa direservasi
     * (katalog & reservasi hanya menerima fasilitas berstatus 'aktif').
     */
    public function markFacilityUnderRepair(Request $request, Report $report)
    {
        $facility = $report->facility;

        if (! $report->isOpen()) {
            return back()->withErrors(['facility' => 'Laporan ini sudah ditutup, fasilitas tidak bisa ditandai dari laporan ini.']);
        }

        if ($facility->status !== 'aktif') {
            return back()->withErrors(['facility' => 'Hanya fasilitas berstatus aktif yang bisa ditandai dalam perbaikan.']);
        }

        DB::transaction(function () use ($request, $report, $facility) {
            $facility->update(['status' => 'dalam_perbaikan']);

            // Fasilitas mulai diperbaiki, jadi laporannya otomatis dianggap sedang diproses.
            if ($report->status === 'baru') {
                $report->update([
                    'status' => 'diproses',
                    'handled_by' => $request->user()->id,
                ]);
            }
        });

        return back()->with('status', "Fasilitas {$facility->name} ditandai sedang dalam perbaikan.");
    }

    /**
     * US 12: kembalikan fasilitas menjadi "aktif" setelah perbaikan selesai.
     */
    public function activateFacility(Report $report)
    {
        $facility = $report->facility;

        if ($report->status !== 'selesai') {
            return back()->withErrors(['facility' => 'Tandai laporan ini selesai terlebih dahulu sebelum mengaktifkan kembali fasilitas.']);
        }

        if ($facility->status !== 'dalam_perbaikan') {
            return back()->withErrors(['facility' => 'Fasilitas ini tidak sedang dalam perbaikan.']);
        }

        $facility->update(['status' => 'aktif']);

        $message = "Fasilitas {$facility->name} kembali aktif dan bisa direservasi.";

        $otherOpen = Report::open()->where('facility_id', $facility->id)->count();
        if ($otherOpen > 0) {
            $message .= " Catatan: masih ada {$otherOpen} laporan lain yang terbuka untuk fasilitas ini.";
        }

        return back()->with('status', $message);
    }

    private function filterCounts(): array
    {
        $statusCounts = Report::query()
            ->selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $counts = [
            'terbuka' => (int) $statusCounts->only(Report::OPEN_STATUSES)->sum(),
            'semua' => (int) $statusCounts->sum(),
        ];

        foreach (array_keys(Report::STATUSES) as $status) {
            $counts[$status] = (int) ($statusCounts[$status] ?? 0);
        }

        return $counts;
    }
}
