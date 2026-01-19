<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Cek apakah pengguna sudah login Dan memiliki peran 'Admin'
        if (Auth::check() && Auth::user()->role == 'Admin') {
            // Jika ya, izinkan untuk melanjutkan ke halaman tujuan
            return $next($request);
        }

        // Jika tidak, paksa logout dan arahkan ke halaman login dengan pesan error
        Auth::logout();
        return redirect()->route('login')->withErrors(['email' => 'Anda tidak memiliki hak akses untuk halaman ini.']);

    }
}
