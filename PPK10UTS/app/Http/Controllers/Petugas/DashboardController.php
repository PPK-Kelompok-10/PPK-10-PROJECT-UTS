<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Report;
use App\Models\Reservation;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $pendingReservations = Reservation::with(['user', 'facility'])
            ->where('status', 'pending')->orderBy('start_time')->orderBy('id')
            ->paginate(15, ['*'], 'pending_page')->withQueryString();
        $approvedReservations = Reservation::with(['user', 'facility'])
            ->where('status', 'approved')->orderBy('start_time')->orderBy('id')
            ->paginate(15, ['*'], 'approved_page')->withQueryString();

        return view('petugas.dashboard', [
            'pendingReservations' => $pendingReservations,
            'pendingCount' => $pendingReservations->total(),
            'approvedReservations' => $approvedReservations,
            'openReportsCount' => Report::open()->count(),
        ]);
    }
}
