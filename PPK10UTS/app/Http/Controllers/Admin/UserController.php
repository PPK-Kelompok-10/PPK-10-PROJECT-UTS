<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    /**
     * Daftar semua user, dipisah per status, untuk halaman admin.
     */
    public function index()
    {
        $pending = User::where('role', 'pengguna')->where('verification_status', 'pending')->latest()->get();
        $verified = User::where('verification_status', 'verified')->latest()->get();
        $rejected = User::where('role', 'pengguna')->where('verification_status', 'rejected')->latest()->get();

        return view('admin.users.index', compact('pending', 'verified', 'rejected'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * US 13 & US 14: Admin mendaftarkan akun Petugas ATAU Pengguna secara langsung.
     * Akun langsung 'verified' karena tidak lewat alur registrasi mandiri.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
            'role' => ['required', 'in:petugas,pengguna'],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'verification_status' => 'verified',
        ]);

        return redirect()->route('admin.users.index')
            ->with('status', 'Akun ' . ucfirst($validated['role']) . ' berhasil didaftarkan.');
    }

    /**
     * US 15: Admin memverifikasi akun pengguna hasil registrasi mandiri.
     */
    public function verify(User $user)
    {
        abort_unless($user->role === 'pengguna' && $user->verification_status === 'pending', 404);

        $user->update(['verification_status' => 'verified']);

        return back()->with('status', "Akun {$user->name} berhasil diverifikasi.");
    }

    /**
     * US 15: Admin menolak akun pengguna hasil registrasi mandiri.
     */
    public function reject(User $user)
    {
        abort_unless($user->role === 'pengguna' && $user->verification_status === 'pending', 404);

        $user->update(['verification_status' => 'rejected']);

        return back()->with('status', "Akun {$user->name} ditolak.");
    }
}
