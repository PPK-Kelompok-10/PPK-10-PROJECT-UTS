<?php
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\FacilityController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Rute untuk scope Anggota 1: Auth, User Management, Facility Master Data
| Rute untuk fitur reservasi & laporan ditambahkan oleh anggota tim lain.
|--------------------------------------------------------------------------
*/
Route::get('/', fn () => redirect()->route('login'));
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

// ----- Pengguna (dashboard umum, dibuat placeholder, dilengkapi anggota lain) -----
Route::middleware(['auth', 'role:pengguna,admin,petugas'])->group(function () {
    Route::get('/dashboard', fn () => view('dashboard'))->name('dashboard');
});

// ----- Petugas -----
Route::middleware(['auth', 'role:petugas'])->group(function () {
    Route::get('/petugas/dashboard', fn () => view('petugas.dashboard'))->name('petugas.dashboard');
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
});
