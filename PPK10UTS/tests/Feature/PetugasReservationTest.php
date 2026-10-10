<?php

use App\Models\Facility;
use App\Models\Reservation;
use App\Models\User;
use Database\Factories\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->petugas = User::create(Arr::except(UserFactory::new()->raw(['role' => 'petugas', 'verification_status' => 'verified']), ['email_verified_at']));
    $this->facility = Facility::create(['name' => 'Aula', 'type' => 'aula', 'location' => 'Kampus', 'capacity' => 50, 'status' => 'aktif']);
    $this->reservation = Reservation::create([
        'user_id' => $this->petugas->id, 'facility_id' => $this->facility->id,
        'purpose' => 'Rapat kampus', 'start_time' => '2026-11-01 10:00:00',
        'end_time' => '2026-11-01 11:00:00', 'status' => 'pending',
    ]);
    $this->actingAs($this->petugas);
});

it('approves only schedules without an approved overlap on the same facility', function ($start, $end, $status, $differentFacility, $allowed) {
    $facility = $differentFacility
        ? Facility::create(['name' => 'Lab', 'type' => 'laboratorium', 'location' => 'Kampus', 'capacity' => 20, 'status' => 'aktif'])
        : $this->facility;
    Reservation::create([
        'user_id' => $this->petugas->id, 'facility_id' => $facility->id,
        'purpose' => 'Existing', 'start_time' => '2026-11-01 '.$start,
        'end_time' => '2026-11-01 '.$end, 'status' => $status,
    ]);
    $this->patch(route('petugas.reservations.approve', $this->reservation))
        ->assertRedirect()->assertSessionHas($allowed ? 'status' : 'error');
    expect($this->reservation->fresh()->status)->toBe($allowed ? 'approved' : 'pending');
})->with([
    'overlap approved' => ['10:30:00', '11:30:00', 'approved', false, false],
    'touching end' => ['11:00:00', '12:00:00', 'approved', false, true],
    'touching start' => ['09:00:00', '10:00:00', 'approved', false, true],
    'other facility' => ['10:30:00', '11:30:00', 'approved', true, true],
    'pending overlap' => ['10:30:00', '11:30:00', 'pending', false, true],
]);

it('approves a pending reservation without conflict', function () {
    $this->patch(route('petugas.reservations.approve', $this->reservation))->assertSessionHas('status');
    expect($this->reservation->fresh()->status)->toBe('approved');
});

it('rejects a pending reservation', function () {
    $this->patch(route('petugas.reservations.reject', $this->reservation))->assertSessionHas('status');
    expect($this->reservation->fresh()->status)->toBe('rejected');
});

it('does not reprocess non-pending reservations', function ($status, $action) {
    $this->reservation->update(['status' => $status]);
    $this->patch(route('petugas.reservations.'.$action, $this->reservation))->assertSessionHas('error');
    expect($this->reservation->fresh()->status)->toBe($status);
})->with(['approved', 'rejected', 'cancelled'])->with(['approve', 'reject']);

it('cancels an approved reservation with a required reason', function () {
    $this->reservation->update(['status' => 'approved']);
    $this->patch(route('petugas.reservations.cancel', $this->reservation), ['reason' => 'Kerusakan listrik'])
        ->assertSessionHas('status');
    expect($this->reservation->fresh())->status->toBe('cancelled')->cancellation_reason->toBe('Kerusakan listrik');
});

it('rejects invalid cancellation reasons', function ($reason) {
    $this->reservation->update(['status' => 'approved']);
    $this->patch(route('petugas.reservations.cancel', $this->reservation), ['reason' => $reason])
        ->assertSessionHasErrors('reason');
    expect($this->reservation->fresh()->status)->toBe('approved');
})->with(['empty' => [''], 'whitespace' => ['   '], 'too long' => [str_repeat('a', 501)], 'not string' => [['alasan']]]);

it('only cancels approved reservations', function ($status) {
    $this->reservation->update(['status' => $status]);
    $this->patch(route('petugas.reservations.cancel', $this->reservation), ['reason' => 'Darurat'])
        ->assertSessionHas('error');
    expect($this->reservation->fresh()->status)->toBe($status);
})->with(['pending', 'rejected', 'cancelled']);

it('blocks other roles from all petugas reservation endpoints', function ($role) {
    $this->petugas->update(['role' => $role]);
    $this->get(route('petugas.dashboard'))->assertForbidden();
    foreach (['approve', 'reject', 'cancel'] as $action) {
        $this->patch(route('petugas.reservations.'.$action, $this->reservation), ['reason' => 'Darurat'])->assertForbidden();
    }
    expect($this->reservation->fresh()->status)->toBe('pending');
})->with(['pengguna', 'admin']);

it('redirects guests to login', function () {
    auth()->logout();
    $this->get(route('petugas.dashboard'))->assertRedirect(route('login'));
    foreach (['approve', 'reject', 'cancel'] as $action) {
        $this->patch(route('petugas.reservations.'.$action, $this->reservation))->assertRedirect(route('login'));
    }
});

it('shows an ordered paginated pending queue and approved reservations', function () {
    for ($i = 0; $i < 16; $i++) {
        Reservation::create([
            'user_id' => $this->petugas->id, 'facility_id' => $this->facility->id,
            'purpose' => 'Antrean '.$i, 'start_time' => '2026-12-01 10:00:00',
            'end_time' => '2026-12-01 11:00:00', 'status' => 'pending',
        ]);
    }
    $this->get(route('petugas.dashboard'))->assertOk()->assertSee('Rapat kampus')->assertSee('Approve')->assertSee('Reject')
        ->assertViewHas('pendingCount', 17)
        ->assertViewHas('pendingReservations', fn ($rows) => $rows->count() === 15 && $rows->first()->id === $this->reservation->id)
        ->assertViewHas('approvedReservations')->assertViewHas('openReports')->assertViewHas('openReportsCount');
    $this->get(route('petugas.dashboard', ['pending_page' => 2]))->assertOk()
        ->assertViewHas('pendingReservations', fn ($rows) => $rows->count() === 2);
    $this->reservation->update(['status' => 'approved']);
    $this->get(route('petugas.dashboard'))->assertOk()->assertSee('Cancel Reservasi')->assertSee('name="reason"', false);
});
