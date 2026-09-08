<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Batasi akses route berdasarkan role user yang login.
     * Contoh pemakaian di routes: ->middleware('role:admin')
     *                             ->middleware('role:admin,petugas')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!in_array($user->role, $roles, true)) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        if ($user->isPengguna() && !$user->isVerified()) {
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['email' => 'Akun Anda belum diverifikasi oleh admin.']);
        }

        return $next($request);
    }
}
