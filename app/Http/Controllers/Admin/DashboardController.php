<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Project;
use App\Models\SchoolClass;
use App\Models\Department;

class DashboardController extends Controller
{
    public function index()
    {
        // [OPTIMASI TAHAP 1]: Menggabungkan query agregat untuk tabel projects
        // Menghemat 1 eksekusi query ke database dengan membiarkan SQL Engine yang menghitungnya sekaligus
        $projectStats = Project::select(
            DB::raw('COUNT(*) as total'),
            DB::raw("SUM(CASE WHEN status = 'ACTIVE' THEN 1 ELSE 0 END) as active")
        )->first();

        // Mengambil statistik ringkas untuk Dashboard Admin
        $stats = [
            'total_users'       => User::count(),
            'total_projects'    => (int) $projectStats->total,
            'active_projects'   => (int) $projectStats->active,
            'total_classes'     => SchoolClass::count(),
            'total_departments' => Department::count(),
        ];

        // [OPTIMASI TAHAP 2 (PENCEGAHAN N+1 FATAL)]:
        // Menambahkan "Nested Eager Loading" dengan titik (.)
        // Memastikan academicYear dan department dari schoolClass juga ikut dimuat.
        // Jika tidak, file blade admin.dashboard bisa meledak saat memanggil $project->schoolClass->academicYear->name
        $recentProjects = Project::with([
            'leader',
            'schoolClass.academicYear',
            'schoolClass.department',
            'schoolClass.level'
        ])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

        return view('admin.dashboard', compact('stats', 'recentProjects'));
    }
}
