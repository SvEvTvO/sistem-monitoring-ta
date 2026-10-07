<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Project;
use App\Models\SchoolClass;
use App\Models\Department;

class DashboardController extends Controller
{
    public function index()
    {
        // Mengambil statistik ringkas untuk Dashboard Admin
        $stats = [
            'total_users' => User::count(),
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'ACTIVE')->count(),
            'total_classes' => SchoolClass::count(),
            'total_departments' => Department::count(),
        ];

        // Mengambil 5 project terbaru untuk tabel sekilas
        $recentProjects = Project::with(['leader', 'schoolClass'])
                                 ->orderBy('created_at', 'desc')
                                 ->take(5)
                                 ->get();

        return view('admin.dashboard', compact('stats', 'recentProjects'));
    }
}
