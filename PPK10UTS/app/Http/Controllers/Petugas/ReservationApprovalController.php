<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Services\CheckScheduleConflictService;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class ReservationApprovalController extends Controller
{
    public function approve(
        Reservation $reservation,
        CheckScheduleConflictService $conflictService
    ) {
        if ($reservation->status !== 'pending') {
            return back()->with(
                'error',
                'Reservasi ini sudah diproses.'
            );
        }

        if ($conflictService->hasConflict($reservation)) {
            return back()->with(
                'error',
                'Reservasi bentrok dengan jadwal yang sudah disetujui.'
            );
        }

        DB::transaction(function () use ($reservation) {
            $reservation->update([
                'status' => 'approved',
            ]);
        });

        return back()->with(
            'status',
            'Reservasi berhasil disetujui.'
        );
    }

    public function reject(Reservation $reservation)
    {
        if ($reservation->status !== 'pending') {
            return back()->with(
                'error',
                'Reservasi ini sudah diproses.'
            );
        }

        DB::transaction(function () use ($reservation) {
            $reservation->update([
                'status' => 'rejected',
            ]);
        });

        return back()->with(
            'status',
            'Reservasi berhasil ditolak.'
        );
    }
    public function cancel(Request $request, Reservation $reservation)
    {
        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        if ($reservation->status !== 'approved') {
            return back()->with(
                'error',
                'Hanya reservasi yang sudah disetujui yang dapat dibatalkan.'
            );
        }

        DB::transaction(function () use ($reservation, $validated) {
            $reservation->update([
                'status' => 'cancelled',
                'cancellation_reason' => $validated['reason'],
            ]);
        });

        return back()->with(
            'status',
            'Reservasi berhasil dibatalkan oleh petugas.'
        );
    }
}