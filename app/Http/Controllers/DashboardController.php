<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Services\ProgressService;
use App\Services\ProjectWeekService;
use App\Models\Report;
use App\Models\ProjectTarget;
use App\Models\Announcement;
use App\Models\ProjectDivision;
use App\Models\User;

class DashboardController extends Controller
{
    public function index(ProgressService $progressService, ProjectWeekService $projectWeekService)
    {
        $user = Auth::user();

        $membership = $user->projectMembers()->with(['project', 'division'])->first();
        $ledProject = $user->ledProjects()->where('status', 'ACTIVE')->first();

        // [OPTIMASI]: Eager load project agar tidak memanggil query ulang jika project diakses
        $ledDivision = $user->ledDivisions()->with('project')->whereHas('project', function($q) {
            $q->where('status', 'ACTIVE');
        })->first();

        // Variabel default
        $projectProgress = 0;
        $currentWeek = null;
        $myReport = null;
        $pendingReviews = 0;
        $upcomingTargets = collect();
        $latestAnnouncement = null;

        // Variabel Chart Ketua Divisi
        $chartLabels = ['Minggu 0'];
        $chartData = [0];

        // Variabel Analitik (Semua Role)
        $divisionLabels = [];
        $divisionProgressData = [];
        $divisionDetails = [];

        // Palet warna tetap untuk penanda tiap divisi
        $colorPalette = ['#0245EC', '#10b981', '#f59e0b', '#8b5cf6', '#ef4444', '#14b8a6', '#f43f5e', '#84cc16'];

        // Variabel Chart Interaktif (Khusus Ketua Project)
        $interactiveChartData = [];

        if ($membership) {
            $project = $membership->project;
            $projectProgress = $progressService->calculateProjectProgress($project);
            $currentWeek = $projectWeekService->getCurrentWeek($project);

            if ($currentWeek) {
                $myReport = Report::where('project_week_id', $currentWeek->id)
                    ->where('author_id', $user->id)
                    ->where('type', 'PERSONAL')
                    ->first();
            }

            if ($ledProject || $ledDivision) {
                $divisionIds = $user->ledDivisions()->pluck('id');
                $projectIds = $user->ledProjects()->pluck('id');

                $pendingReviews = Report::where(function($query) use ($divisionIds, $projectIds) {
                        $query->whereIn('division_id', $divisionIds)->where('type', 'PERSONAL');
                    })->orWhere(function($query) use ($projectIds) {
                        $query->whereIn('project_id', $projectIds)->where('type', 'DIVISION');
                    })
                    ->where('author_id', '!=', $user->id)
                    ->where('status', 'SUBMITTED')
                    ->count();
            }

            // Data untuk Chart Khusus Ketua Divisi
            if ($ledDivision) {
                $reportsPerWeek = Report::where('division_id', $ledDivision->id)
                    ->where('type', 'PERSONAL')
                    ->selectRaw('project_week_id, count(*) as total')
                    ->groupBy('project_week_id')
                    ->with('projectWeek')
                    ->get()
                    ->sortBy(function($report) {
                        return $report->projectWeek->week_number ?? 0;
                    });

                foreach ($reportsPerWeek as $report) {
                    if ($report->projectWeek) {
                        $chartLabels[] = 'Minggu ' . $report->projectWeek->week_number;
                        $chartData[] = $report->total;
                    }
                }
            }

            // =========================================================================
            // [OPTIMASI TAHAP 1]: Tarik Daftar Divisi
            // =========================================================================
            $divisions = ProjectDivision::where('project_id', $project->id)->get();

            // =========================================================================
            // [OPTIMASI TAHAP 2]: Cegah N+1 pada pencarian Nama Ketua Divisi
            // Menarik semua user yang menjadi ketua sekaligus dalam 1 Query menggunakan whereIn
            // =========================================================================
            $leaderIds = $divisions->pluck('leader_user_id')->filter()->unique();
            $leaders = User::whereIn('id', $leaderIds)->get()->keyBy('id');

            // =========================================================================
            // [OPTIMASI TAHAP 3]: Tarik Semua Laporan Divisi sekaligus (Bukan 1 per 1 di dalam loop)
            // =========================================================================
            $allApprovedDivisionReports = Report::where('project_id', $project->id)
                ->where('type', 'DIVISION')
                ->where('status', 'APPROVED')
                ->get()
                ->groupBy('division_id')
                ->map(function($reports) {
                    return $reports->sortByDesc('project_week_id')->first();
                });

            // =========================================================================
            // [OPTIMASI TAHAP 4]: Tarik Semua Data Chart untuk Ketua Project sekaligus
            // =========================================================================
            $allDivReportsPerWeek = collect();
            if ($ledProject) {
                $allDivReportsPerWeek = Report::where('project_id', $project->id)
                    ->where('type', 'PERSONAL')
                    ->selectRaw('division_id, project_week_id, count(*) as total')
                    ->groupBy('division_id', 'project_week_id')
                    ->with('projectWeek')
                    ->get()
                    ->groupBy('division_id');
            }

            // Loop divisi sekarang 100% AMAN DARI N+1 QUERY!
            foreach ($divisions as $index => $div) {
                $color = $colorPalette[$index % count($colorPalette)];

                // Ambil data yang sudah ditarik di atas
                $latestReport = $allApprovedDivisionReports->get($div->id);
                $leader = $leaders->get($div->leader_user_id);

                $prog = $latestReport ? (float) $latestReport->progress_percentage : 0;

                $divisionLabels[] = $div->name;
                $divisionProgressData[] = $prog;

                $divisionDetails[] = (object) [
                    'id' => $div->id,
                    'name' => $div->name,
                    'leader_name' => $leader ? $leader->name : 'Belum ada ketua',
                    'progress' => $prog,
                    'last_update' => $latestReport ? $latestReport->created_at->diffForHumans() : 'Belum ada',
                    'color' => $color
                ];

                // Data chart interaktif tiap divisi jika user adalah Ketua Project
                if ($ledProject) {
                    $divReportsPerWeek = $allDivReportsPerWeek->get($div->id, collect())
                        ->sortBy(function($r) {
                            return $r->projectWeek->week_number ?? 0;
                        });

                    $iLabels = ['Minggu 0'];
                    $iData = [0];
                    foreach ($divReportsPerWeek as $r) {
                        if ($r->projectWeek) {
                            $iLabels[] = 'Minggu ' . $r->projectWeek->week_number;
                            $iData[] = $r->total;
                        }
                    }

                    $interactiveChartData[$div->id] = [
                        'name' => $div->name,
                        'color' => $color,
                        'labels' => $iLabels,
                        'data' => $iData
                    ];
                }
            }

            // Target Project
            $upcomingTargets = ProjectTarget::where('project_id', $project->id)
                ->whereNull('completed_at')
                ->orderBy('deadline', 'asc')
                ->take(3)->get();

            // Pengumuman Terbaru
            $latestAnnouncement = Announcement::where('project_id', $project->id)
                ->where(function($query) use ($membership) {
                    $query->where('audience_type', 'ALL_PROJECT')
                          ->orWhere('division_id', $membership->division_id);
                })->latest()->first();
        }

        return view('dashboard', compact(
            'user', 'membership', 'ledProject', 'ledDivision', 'projectProgress',
            'currentWeek', 'myReport', 'pendingReviews', 'upcomingTargets', 'latestAnnouncement',
            'chartLabels', 'chartData', 'divisionLabels', 'divisionProgressData', 'divisionDetails', 'interactiveChartData'
        ));
    }
}
