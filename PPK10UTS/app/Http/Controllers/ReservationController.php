<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreReservationRequest;
use App\Models\Facility;
use App\Models\Reservation;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * US 3: form pengajuan reservasi untuk fasilitas tertentu.
     */
    public function create(Request $request, Facility $facility)
    {
        abort_if($facility->status !== 'aktif', 422, 'Fasilitas ini sedang tidak dapat direservasi.');

        // Reservasi wajib H-1, jadi default tanggal di form adalah besok, bukan hari ini.
        $date = $request->filled('date') ? $request->date : now()->addDay()->toDateString();
        $startTime = $request->query('start');
        $endTime = $request->query('end');

        return view('reservations.create', compact('facility', 'date', 'startTime', 'endTime'));
    }

    /**
     * US 3: simpan reservasi baru, status awal selalu 'pending' — proses
     * setuju/tolak dilakukan Petugas (bagian Anggota 3).
     */
    public function store(StoreReservationRequest $request, Facility $facility)
    {
        abort_if($facility->status !== 'aktif', 422, 'Fasilitas ini sedang tidak dapat direservasi.');

        $start = $request->startDateTime();
        $end = $request->endDateTime();

        // Cek dasar di sisi Pengguna: apakah slot ini SUDAH pasti bentrok dengan reservasi
        // lain yang masih aktif (pending/approved) pada fasilitas yang sama. Pengecekan
        // bentrok yang lebih menyeluruh saat approval tetap jadi tanggung jawab Anggota 3
        // (CheckScheduleConflictService), ini hanya validasi awal supaya user langsung tahu.
        $conflict = Reservation::active()
            ->where('facility_id', $facility->id)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();

        if ($conflict) {
            return back()->withErrors([
                'start_time' => 'Slot waktu ini sudah dipesan / masih menunggu proses. Pilih slot lain.',
            ])->withInput();
        }

        Reservation::create([
            'user_id' => $request->user()->id,
            'facility_id' => $facility->id,
            'purpose' => $request->purpose,
            'start_time' => $start,
            'end_time' => $end,
            'status' => 'pending',
        ]);

        return redirect()->route('reservations.index')
            ->with('status', 'Reservasi berhasil diajukan, menunggu diproses petugas.');
    }

    /**
     * US 5: riwayat & status reservasi milik user yang login.
     */
    public function index(Request $request)
    {
        $reservations = Reservation::with('facility')
            ->ownedBy($request->user())
            ->latest('start_time')
            ->paginate(10);

        return view('reservations.index', compact('reservations'));
    }

    /**
     * US 4: pembatalan mandiri sebelum batas waktu tertentu (2 jam sebelum mulai).
     */
    public function cancel(Request $request, Reservation $reservation)
    {
        abort_unless($reservation->canBeCancelledBy($request->user()), 403,
            'Reservasi ini tidak dapat dibatalkan (bukan milik Anda, sudah lewat batas waktu, atau sudah selesai diproses).');

        $reservation->update(['status' => 'cancelled']);

        return back()->with('status', 'Reservasi berhasil dibatalkan.');
    }
}
