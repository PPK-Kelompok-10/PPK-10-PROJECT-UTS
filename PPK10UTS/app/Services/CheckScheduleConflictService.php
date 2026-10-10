<?php

namespace App\Services;

use App\Models\Reservation;

class CheckScheduleConflictService
{
    public function hasConflict(Reservation $reservation, bool $lock = false): bool
    {
        $query = Reservation::where('facility_id', $reservation->facility_id)
            ->where('status', 'approved')
            ->where('id', '!=', $reservation->id)
            ->where('start_time', '<', $reservation->end_time)
            ->where('end_time', '>', $reservation->start_time);

        return $lock
            ? $query->lockForUpdate()->first(['id']) !== null
            : $query->exists();
    }
}
