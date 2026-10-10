<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\Reservation;
use App\Services\CheckScheduleConflictService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReservationApprovalController extends Controller
{
    public function approve(Reservation $reservation, CheckScheduleConflictService $conflictService): RedirectResponse
    {
        $error = DB::transaction(function () use ($reservation, $conflictService): ?string {
            // Serialize approvals even when no approved reservation exists yet.
            Facility::whereKey($reservation->facility_id)->lockForUpdate()->firstOrFail();
            $reservation = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();

            if ($reservation->status !== 'pending') {
                return 'Reservasi ini sudah diproses.';
            }
            if ($conflictService->hasConflict($reservation, true)) {
                return 'Reservasi bentrok dengan jadwal yang sudah disetujui.';
            }
            $reservation->update(['status' => 'approved']);

            return null;
        });

        return $error ? back()->with('error', $error) : back()->with('status', 'Reservasi berhasil disetujui.');
    }

    public function reject(Reservation $reservation): RedirectResponse
    {
        $error = DB::transaction(function () use ($reservation): ?string {
            $reservation = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            if ($reservation->status !== 'pending') {
                return 'Reservasi ini sudah diproses.';
            }
            $reservation->update(['status' => 'rejected']);

            return null;
        });

        return $error ? back()->with('error', $error) : back()->with('status', 'Reservasi berhasil ditolak.');
    }

    public function cancel(Request $request, Reservation $reservation): RedirectResponse
    {
        $validated = $request->validate(['reason' => 'required|string|max:500']);
        $error = DB::transaction(function () use ($reservation, $validated): ?string {
            $reservation = Reservation::whereKey($reservation->id)->lockForUpdate()->firstOrFail();
            if ($reservation->status !== 'approved') {
                return 'Hanya reservasi yang sudah disetujui yang dapat dibatalkan.';
            }
            $reservation->update([
                'status' => 'cancelled',
                'cancellation_reason' => $validated['reason'],
            ]);

            return null;
        });

        return $error ? back()->with('error', $error) : back()->with('status', 'Reservasi berhasil dibatalkan oleh petugas.');
    }
}
