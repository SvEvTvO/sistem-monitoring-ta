<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureProjectMember
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Cek apakah user ini Ketua Project ATAU Anggota Project/Divisi
        $hasProject = $user->ledProjects()->exists() || $user->projectMembers()->exists();

        // Jika tidak punya project (Nganggur), lempar paksa ke Ruang Tunggu
        if (!$hasProject) {
            return redirect()->route('dashboard.waiting');
        }

        return $next($request);
    }
}