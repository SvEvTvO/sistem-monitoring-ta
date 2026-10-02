<?php

namespace App\Http\Controllers;

use App\Models\ProjectTarget;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ProjectTargetController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $membership = $user->projectMembers()->with(['project', 'division'])->first();
        if (!$membership) return redirect()->route('dashboard');

        $project = $membership->project;
        $division = $membership->division;

        $isProjectLeader = $project->project_leader_id === $user->id;
        $ledDivision = $user->ledDivisions()->where('project_id', $project->id)->first();
        $isDivisionLeader = $ledDivision ? true : false;
        
        $canCreate = $isProjectLeader || $isDivisionLeader;

        $query = ProjectTarget::where('project_id', $project->id);

        if (!$isProjectLeader) {
            $query->where(function($q) use ($division) {
                $q->whereNull('division_id')->orWhere('division_id', $division->id);
            });
        }

        // --- FILTERING ---
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('scope')) {
            if ($request->scope == 'GLOBAL') $query->whereNull('division_id');
            if ($request->scope == 'DIVISION') $query->whereNotNull('division_id');
        }

        if ($request->filled('status')) {
            $now = Carbon::now();
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

        // --- STATISTIK ---
        $allTargets = ProjectTarget::where('project_id', $project->id);
        if (!$isProjectLeader) {
            $allTargets->where(function($q) use ($division) {
                $q->whereNull('division_id')->orWhere('division_id', $division->id);
            });
        }
        $allTargetsData = $allTargets->get();
        
        $now = Carbon::now();
        $totalTargets = $allTargetsData->count();
        $completedTargets = $allTargetsData->whereNotNull('completed_at')->count();
        $activeTargets = $allTargetsData->whereNull('completed_at')->filter(function($t) use ($now) {
            return Carbon::parse($t->start_date)->startOfDay() <= $now && Carbon::parse($t->deadline)->endOfDay() >= $now;
        })->count();
        $overdueTargets = $allTargetsData->whereNull('completed_at')->filter(function($t) use ($now) {
            return Carbon::parse($t->deadline)->endOfDay() < $now;
        })->count();
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
            'description' => 'nullable|string', // <-- Tambahan
            'start_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:start_date',
        ]);
        

        $user = Auth::user();
        $project = \App\Models\Project::findOrFail($validated['project_id']);
        
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

    // Fungsi detail (View akan kita buat di langkah selanjutnya)
    public function show(ProjectTarget $target)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        // Cek apakah user berhak mengedit target ini
        $isProjectLeader = $target->project->project_leader_id === $user->id;
        $isDivisionLeader = $target->division_id && $user->ledDivisions()->where('id', $target->division_id)->exists();
        $canEdit = $isProjectLeader || $isDivisionLeader;

        return view('targets.show', compact('target', 'canEdit'));
    }

    public function edit(ProjectTarget $target)
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        
        $isProjectLeader = $target->project->project_leader_id === $user->id;
        $isDivisionLeader = $target->division_id && $user->ledDivisions()->where('id', $target->division_id)->exists();

        // Tolak akses jika bukan pembuat/pemilik wewenang target ini
        if (!$isProjectLeader && !$isDivisionLeader) {
            abort(403, 'Akses ditolak. Anda tidak berhak mengedit target ini.');
        }

        return view('targets.edit', compact('target', 'isProjectLeader'));
    }

    public function update(Request $request, ProjectTarget $target)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:200',
            'description' => 'nullable|string', // <-- Tambahan
            'start_date' => 'required|date',
            'deadline' => 'required|date|after_or_equal:start_date',
        ]);

        $target->update($validated);
        return redirect()->route('targets.index')->with('success', 'Target berhasil diperbarui.');
    }

    public function complete(ProjectTarget $target)
    {
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
        $user = Auth::user();
        
        // Verifikasi hak akses (Ketua Project atau Ketua Divisi)
        $isProjectLeader = $target->project->project_leader_id === $user->id;
        $isDivisionLeader = $target->division_id && $user->ledDivisions()->where('id', $target->division_id)->exists();

        if (!$isProjectLeader && !$isDivisionLeader) {
            abort(403, 'Akses ditolak.');
        }

        // Keamanan Tambahan: Pastikan target benar-benar BELUM DIMULAI
        $start = \Carbon\Carbon::parse($target->start_date)->startOfDay();
        if (!$start->isFuture() || !is_null($target->completed_at)) {
            return back()->with('error', 'Gagal! Hanya target yang belum dimulai yang dapat dihapus.');
        }

        $target->delete();

        return redirect()->route('targets.index')->with('success', 'Target berhasil dihapus secara permanen.');
    }

    
}