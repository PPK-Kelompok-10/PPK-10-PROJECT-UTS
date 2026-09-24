<?php
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FacilityController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Petugas\ReportResolutionController;
use App\Http\Controllers\Admin\ExportReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute untuk scope Anggota 1: Auth, User Management, Facility Master Data
| Rute untuk scope Anggota 2: Katalog Fasilitas & Reservasi (ditambahkan di sini
| karena di-take over dari anggota yang berhalangan)
| Rute untuk fitur laporan/approval Petugas ditambahkan oleh anggota tim lain.
| Rute untuk scope Anggota 4: Laporan Kerusakan, Penanganan Petugas, Rekap Admin
|--------------------------------------------------------------------------
*/

// Halaman utama: katalog fasilitas (US 1 & 2), bisa diakses Pengunjung tanpa login.
Route::get('/', [CatalogController::class, 'index'])->name('home');

// ----- Guest (Pengunjung) -----
Route::middleware('guest')->group(function () {
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

// ----- Katalog Fasilitas (US 1 & 2) — publik, tanpa auth, diakses Pengunjung & Pengguna -----
Route::get('/facilities', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/facilities/{facility}', [CatalogController::class, 'show'])->name('catalog.show');

// ----- Pengguna (dashboard umum, dilengkapi anggota lain) -----
Route::middleware(['auth', 'role:pengguna,admin,petugas'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
});

// ----- Reservasi (US 3, 4, 5) — khusus role Pengguna -----
Route::middleware(['auth', 'role:pengguna'])->group(function () {
    Route::get('/facilities/{facility}/reserve', [ReservationController::class, 'create'])->name('reservations.create');
    Route::post('/facilities/{facility}/reserve', [ReservationController::class, 'store'])->name('reservations.store');
    Route::get('/reservations', [ReservationController::class, 'index'])->name('reservations.index');
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'cancel'])->name('reservations.cancel');
});

// ----- Laporan Kerusakan (US 6 & 7) — khusus role Pengguna -----
Route::middleware(['auth', 'role:pengguna'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/create', [ReportController::class, 'create'])->name('reports.create');
    Route::post('/reports', [ReportController::class, 'store'])->name('reports.store');
    Route::get('/reports/{report}', [ReportController::class, 'show'])->name('reports.show');
});

// Foto bukti disimpan di disk privat, jadi diakses lewat route ini.
// Controller mengecek: hanya pelapor, Petugas, dan Admin yang boleh melihat.
Route::get('/reports/{report}/photo', [ReportController::class, 'photo'])
    ->middleware(['auth', 'role:pengguna,petugas,admin'])
    ->name('reports.photo');

// ----- Petugas -----
Route::get('/reports/{report}/photo', [ReportController::class, 'photo'])
    ->middleware(['auth', 'role:pengguna,petugas,admin'])
    ->name('reports.photo');

// ----- Petugas -----

Route::middleware(['auth', 'role:petugas'])->group(function () {
    Route::get('/petugas/dashboard', function () {
        $pendingReservations = \App\Models\Reservation::with(['facility', 'user'])
            ->where('status', 'pending')
            ->latest('start_time')
            ->take(10)
            ->get();

        // Antrean laporan kerusakan (Anggota 4): yang paling lama menunggu tampil paling atas.
        $openReports = \App\Models\Report::with(['facility', 'user'])
            ->open()
            ->oldest()
            ->take(10)
            ->get();

        return view('petugas.dashboard', [
            'pendingReservations' => $pendingReservations,
            'pendingCount' => \App\Models\Reservation::where('status', 'pending')->count(),
            'openReports' => $openReports,
            'openReportsCount' => \App\Models\Report::open()->count(),
        ]);
    })->name('petugas.dashboard');
});

// ----- Petugas: penanganan laporan & status fasilitas (US 11 & 12) -----
Route::prefix('petugas')->name('petugas.')->middleware(['auth', 'role:petugas'])->group(function () {
    Route::get('/reports', [ReportResolutionController::class, 'index'])->name('reports.index');
    Route::get('/reports/{report}', [ReportResolutionController::class, 'show'])->name('reports.show');
    Route::patch('/reports/{report}', [ReportResolutionController::class, 'update'])->name('reports.update');
    Route::patch('/reports/{report}/facility/repair', [ReportResolutionController::class, 'markFacilityUnderRepair'])->name('reports.facility.repair');
    Route::patch('/reports/{report}/facility/activate', [ReportResolutionController::class, 'activateFacility'])->name('reports.facility.activate');
});

// ----- Admin -----
Route::prefix('admin')->name('admin.')->middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/dashboard', fn () => view('admin.dashboard'))->name('dashboard');

    // US 13 & 14: registrasi langsung akun Petugas / Pengguna
    // US 15: verifikasi/tolak akun hasil registrasi mandiri
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::patch('/users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
    Route::patch('/users/{user}/reject', [UserController::class, 'reject'])->name('users.reject');

    // US 16: kelola data master fasilitas
    Route::get('/facilities', [FacilityController::class, 'index'])->name('facilities.index');
    Route::get('/facilities/create', [FacilityController::class, 'create'])->name('facilities.create');
    Route::post('/facilities', [FacilityController::class, 'store'])->name('facilities.store');
    Route::get('/facilities/{facility}/edit', [FacilityController::class, 'edit'])->name('facilities.edit');
    Route::put('/facilities/{facility}', [FacilityController::class, 'update'])->name('facilities.update');
    Route::patch('/facilities/{facility}/disable', [FacilityController::class, 'disable'])->name('facilities.disable');
    Route::patch('/facilities/{facility}/activate', [FacilityController::class, 'activate'])->name('facilities.activate');

    // US 17: rekap okupansi & frekuensi kerusakan + ekspor CSV/PDF
    Route::get('/exports', [ExportReportController::class, 'index'])->name('exports.index');
    Route::get('/exports/csv', [ExportReportController::class, 'csv'])->name('exports.csv');
    Route::get('/exports/pdf', [ExportReportController::class, 'pdf'])->name('exports.pdf');
});
