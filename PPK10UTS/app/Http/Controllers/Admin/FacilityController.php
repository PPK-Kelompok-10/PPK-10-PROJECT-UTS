<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function index()
    {
        $facilities = Facility::latest()->paginate(10);

        return view('admin.facilities.index', compact('facilities'));
    }

    public function create()
    {
        return view('admin.facilities.create');
    }

    public function store(Request $request)
    {
        $validated = $this->validateFacility($request);

        Facility::create($validated);

        return redirect()->route('admin.facilities.index')
            ->with('status', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit(Facility $facility)
    {
        return view('admin.facilities.edit', compact('facility'));
    }

    public function update(Request $request, Facility $facility)
    {
        $validated = $this->validateFacility($request);

        $facility->update($validated);

        return redirect()->route('admin.facilities.index')
            ->with('status', 'Fasilitas berhasil diperbarui.');
    }

    /**
     * US 16: Admin menonaktifkan fasilitas (soft-disable, bukan hard delete,
     * supaya riwayat reservasi/laporan yang terhubung tetap konsisten).
     */
    public function disable(Facility $facility)
    {
        $facility->update(['status' => 'nonaktif']);

        return back()->with('status', "Fasilitas {$facility->name} dinonaktifkan.");
    }

    public function activate(Facility $facility)
    {
        $facility->update(['status' => 'aktif']);

        return back()->with('status', "Fasilitas {$facility->name} diaktifkan kembali.");
    }

    private function validateFacility(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'string', 'max:100'],
            'location' => ['required', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:0'],
            'description' => ['nullable', 'string'],
        ]);
    }
}
