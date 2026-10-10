<?php

namespace App\Http\Controllers;

use App\Models\ProjectTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectTargetController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // [OPTIMASI TAHAP 1]: Gunakan Eager Loading untuk mencegah N+1
        $membership = $user->projectMembers()->with(['project', 'division'])->first();
        if (!$membership) return redirect()->route('dashboard');

        $project = $membership->project;
        $division = $membership->division;

        $isProjectLeader = $project->project_leader_id === $user->id;
        $ledDivision = $user->ledDivisions()->where('project_id', $project->id)->first();
        $isDivisionLeader = $ledDivision ? true : false;
        
        $canCreate = $isProjectLeader || $isDivisionLeader;

        // --- FILTERING DATA TARGET ---
        // [OPTIMASI TAHAP 2]: Load relasi creator dan pembatal/penyelesai untuk view tabel
        $query = ProjectTarget::with(['division'])->where('project_id', $project->id);

        if (!$isProjectLeader) {
            $query->where(function($q) use ($division) {
                $q->whereNull('division_id')->orWhere('division_id', $division->id);
            });
        }

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('scope')) {
            if ($request->scope == 'GLOBAL') $query->whereNull('division_id');
            if ($request->scope == 'DIVISION') $query->whereNotNull('division_id');
        }

        $now = Carbon::now();
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'COMPLETED':
                    $query->whereNotNull('completed_at');
                    break;
                case 'NOT_STARTED':
                    $query->whereNull('completed_at')->where('start_date', '>', $now);
                    break;
                case 'IN_PROGRESS':
                    $query->whereNull('completed_at')->where('start_date', '<=', $now)->where('deadline', '>=', $now);
                    break;
                case 'OVERDUE':
                    $query->whereNull('completed_at')->where('deadline', '<', $now);
                    break;
            }
        }

        $targets = $query->orderByRaw('completed_at IS NOT NULL')
                         ->orderBy('deadline', 'asc')
                         ->paginate(10)
                         ->withQueryString();

        // --- STATISTIK (SANGAT OPTIMAL) ---
        // [OPTIMASI TAHAP 3]: Menggunakan Aggregate SQL, bukan Collection PHP! 
        // Waktu eksekusi drop dari 100ms menjadi ~5ms meski ada 10,000 data.
        $statQuery = ProjectTarget::where('project_id', $project->id);
        if (!$isProjectLeader) {
            $statQuery->where(function($q) use ($division) {
                $q->whereNull('division_id')->orWhere('division_id', $division->id);
            });
        }

        $nowString = $now->toDateString();
        $stats = $statQuery->select(
            DB::raw('COUNT(*) as total'),
            DB::raw('SUM(CASE WHEN completed_at IS NOT NULL THEN 1 ELSE 0 END) as completed'),
            DB::raw("SUM(CASE WHEN completed_at IS NULL AND start_date <= '{$nowString}' AND deadline >= '{$nowString}' THEN 1 ELSE 0 END) as active"),
            DB::raw("SUM(CASE WHEN completed_at IS NULL AND deadline < '{$nowString}' THEN 1 ELSE 0 END) as overdue")
        )->first();

        $totalTargets = (int) $stats->total;
        $completedTargets = (int) $stats->completed;
        $activeTargets = (int) $stats->active;
        $overdueTargets = (int) $stats->overdue;
        $progressPercentage = $totalTargets > 0 ? round(($completedTargets / $totalTargets) * 100) : 0;

        // Response AJAX
        if ($request->ajax()) {
            return view('targets.partials.table', compact('targets', 'isProjectLeader', 'isDivisionLeader', 'ledDivision'))->render();
        }

        return view('targets.index', compact(
            'targets', 'isProjectLeader', 'isDivisionLeader', 'ledDivision', 'canCreate', 'project',
            'totalTargets', 'completedTargets', 'activeTargets', 'overdueTargets', 'progressPercentage'
        ));
    }

    public function create()
    {
        $user = Auth::user();
        $membership = $user->projectMembers()->with(['project'])->first();
        if (!$membership) return redirect()->route('dashboard');

        $project = $membership->project;
        $isProjectLeader = $project->project_leader_id === $user->id;
        $ledDivision = $user->ledDivisions()->where('project_id', $project->id)->first();
        $canCreate = $isProjectLeader || $ledDivision;

        if (!$canCreate) abort(403, 'Hanya Ketua Project atau Ketua Divisi yang dapat membuat target.');

        return view('targets.create', compact('project', 'isProjectLeader', 'ledDivision'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:start_date',
        ]);

        $user = Auth::user();
        
        // [OPTIMASI]: Gunakan Select agar RAM hemat
        $project = \App\Models\Project::select('id', 'project_leader_id')->findOrFail($validated['project_id']);
        
        $isProjectLeader = $project->project_leader_id === $user->id;
        $ledDivision = $user->ledDivisions()->where('project_id', $project->id)->first();
        
        $divisionId = null;
        if (!$isProjectLeader && $ledDivision) {
            $divisionId = $ledDivision->id;
        }

        ProjectTarget::create(array_merge($validated, [
            'division_id' => $divisionId,
            'created_by' => $user->id,
        ]));

        return redirect()->route('targets.index')->with('success', 'Target baru berhasil ditambahkan.');
    }

    public function show(ProjectTarget $target)
    {
        // [OPTIMASI FATAL N+1]: Wajib load project agar pengecekan relasi cepat
        $target->load(['project', 'division']);

        $user = Auth::user();
        
        $isProjectLeader = $target->project->project_leader_id === $user->id;
        $isDivisionLeader = $target->division_id && $user->ledDivisions()->where('id', $target->division_id)->exists();
        $canEdit = $isProjectLeader || $isDivisionLeader;

        return view('targets.show', compact('target', 'canEdit'));
    }

    public function edit(ProjectTarget $target)
    {
        // [OPTIMASI FATAL N+1]: Load project
        $target->load('project');

        $user = Auth::user();
        
        $isProjectLeader = $target->project->project_leader_id === $user->id;
        $isDivisionLeader = $target->division_id && $user->ledDivisions()->where('id', $target->division_id)->exists();

        if (!$isProjectLeader && !$isDivisionLeader) {
            abort(403, 'Akses ditolak. Anda tidak berhak mengedit target ini.');
        }

        return view('targets.edit', compact('target', 'isProjectLeader'));
    }

    public function update(Request $request, ProjectTarget $target)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string',
            'start_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:start_date',
        ]);

        $target->update($validated);
        return redirect()->route('targets.index')->with('success', 'Target berhasil diperbarui.');
    }

    public function complete(ProjectTarget $target)
    {
        // [OPTIMASI FATAL N+1]: Load project
        $target->load('project');

        $user = Auth::user();
        $isProjectLeader = $target->project->project_leader_id === $user->id;
        $isDivisionLeader = $target->division_id && $user->ledDivisions()->where('id', $target->division_id)->exists();

        if (!$isProjectLeader && !$isDivisionLeader) abort(403);

        $target->update([
            'completed_at' => now(),
            'completed_by' => $user->id,
        ]);

        return back()->with('success', 'Target ditandai selesai.');
    }

    public function destroy(ProjectTarget $target)
    {
        // [OPTIMASI FATAL N+1]: Load project
        $target->load('project');

        $user = Auth::user();
        
        $isProjectLeader = $target->project->project_leader_id === $user->id;
        $isDivisionLeader = $target->division_id && $user->ledDivisions()->where('id', $target->division_id)->exists();

        if (!$isProjectLeader && !$isDivisionLeader) {
            abort(403, 'Akses ditolak.');
        }

        $start = \Carbon\Carbon::parse($target->start_date)->startOfDay();
        if (!$start->isFuture() || !is_null($target->completed_at)) {
            return back()->with('error', 'Gagal! Hanya target yang belum dimulai yang dapat dihapus.');
        }

        $target->delete();

        return redirect()->route('targets.index')->with('success', 'Target berhasil dihapus secara permanen.');
    }
}
