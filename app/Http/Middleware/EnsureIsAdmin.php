<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Jika belum login ATAU dia bukan admin, tolak aksesnya!
        if (!auth()->check() || !auth()->user()->is_admin) {
            abort(403, 'Akses Ditolak! Anda bukan Administrator Sistem.');
        }

        return $next($request);
    }
}
