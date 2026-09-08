<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    /**
     * Registrasi mandiri — HANYA untuk role 'pengguna' (mahasiswa/dosen/staf).
     * Petugas TIDAK PERNAH melakukan registrasi mandiri (US 13).
     * Akun langsung berstatus 'pending' menunggu verifikasi admin (US 15).
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'confirmed', Password::min(8)->letters()->numbers()],
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'pengguna',
            'verification_status' => 'pending',
        ]);

        return redirect()->route('login')
            ->with('status', 'Registrasi berhasil. Akun Anda menunggu verifikasi admin sebelum dapat digunakan untuk login.');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (!Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'Email atau password salah.',
            ])->onlyInput('email');
        }

        $user = Auth::user();

        // Blokir login untuk akun pengguna yang belum diverifikasi / ditolak.
        if ($user->isPengguna() && !$user->isVerified()) {
            Auth::logout();
            return back()->withErrors([
                'email' => $user->verification_status === 'pending'
                    ? 'Akun Anda masih menunggu verifikasi admin.'
                    : 'Akun Anda ditolak oleh admin. Silakan hubungi admin fasilitas.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPathForRole($user->role));
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah logout.');
    }

    private function redirectPathForRole(string $role): string
    {
        return match ($role) {
            'admin' => route('admin.dashboard'),
            'petugas' => route('petugas.dashboard'),
            default => route('dashboard'),
        };
    }
}
