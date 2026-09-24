<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReportRequest;
use App\Models\Facility;
use App\Models\Report;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReportController extends Controller
{
    /**
     * US 7: daftar laporan milik Pengguna yang login beserta statusnya.
     */
    public function index(Request $request)
    {
        $reports = Report::with('facility')
            ->ownedBy($request->user())
            ->latest()
            ->paginate(10);

        return view('reports.index', compact('reports'));
    }

    /**
     * US 6: form laporan kerusakan. Bisa dibuka dengan ?facility={id}
     * supaya fasilitasnya langsung terpilih (misalnya dari halaman detail katalog).
     */
    public function create(Request $request)
    {
        $facilities = Facility::query()
            ->whereIn('status', Report::REPORTABLE_FACILITY_STATUSES)
            ->orderBy('name')
            ->get(['id', 'name', 'location', 'status']);

        $selectedFacilityId = $request->integer('facility') ?: null;
        $categories = Report::CATEGORIES;

        return view('reports.create', compact('facilities', 'selectedFacilityId', 'categories'));
    }

    /**
     * US 6: simpan laporan baru. Foto disimpan di disk privat,
     * status awal selalu 'baru' dan diproses Petugas (US 11).
     */
    public function store(StoreReportRequest $request)
    {
        $photoPath = $request->file('photo')->store(Report::PHOTO_DIRECTORY, Report::PHOTO_DISK);

        $report = Report::create([
            'user_id' => $request->user()->id,
            'facility_id' => $request->validated('facility_id'),
            'category' => $request->validated('category'),
            'description' => $request->validated('description'),
            'photo_path' => $photoPath,
            'status' => 'baru',
        ]);

        return redirect()->route('reports.show', $report)
            ->with('status', 'Laporan berhasil dikirim dan akan segera ditangani petugas.');
    }

    /**
     * US 7: detail laporan + catatan resolusi dari Petugas.
     */
    public function show(Request $request, Report $report)
    {
        abort_unless($report->isOwnedBy($request->user()), 403, 'Laporan ini bukan milik Anda.');

        $report->load(['facility', 'handler']);

        return view('reports.show', compact('report'));
    }

    /**
     * Menampilkan foto bukti. Karena file ada di disk privat, akses dicek dulu:
     * hanya pelapor, Petugas, dan Admin yang boleh melihat.
     */
    public function photo(Request $request, Report $report)
    {
        abort_unless($report->canBeViewedBy($request->user()), 403);

        $disk = Storage::disk(Report::PHOTO_DISK);
        abort_unless($report->photo_path && $disk->exists($report->photo_path), 404);

        return $disk->response($report->photo_path);
    }
}
