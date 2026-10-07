<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        // Mengambil log terbaru. Jika tabel audit_logs milikmu punya relasi ke User, bisa ditambahkan with('user')
        $logs = AuditLog::orderBy('created_at', 'desc')->paginate(20);
        
        return view('admin.audit-logs.index', compact('logs'));
    }
}
