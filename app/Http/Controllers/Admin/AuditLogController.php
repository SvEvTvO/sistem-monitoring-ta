<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        // [OPTIMASI FATAL N+1]: Gunakan Eager Loading 'with("user")'
        // Ini mencegah Laravel melakukan query berulang (N+1) saat merender nama pelaku di file Blade.
        $logs = AuditLog::with('user')
                        ->orderBy('created_at', 'desc')
                        ->paginate(20);

        return view('admin.audit-logs.index', compact('logs'));
    }
}
