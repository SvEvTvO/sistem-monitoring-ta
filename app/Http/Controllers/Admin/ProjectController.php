<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Project;
use App\Models\SchoolClass;
use App\Models\User;

class ProjectController extends Controller
{
    public function index(Request $request)
    {
        $query = Project::with(['leader', 'schoolClass.department', 'schoolClass.level']);

        if ($request->filled('search')) {
            $query->where('name', 'like', "%{$request->search}%");
        }

        $projects = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        // Data untuk dropdown di Modal
        $classes = SchoolClass::with(['department', 'level'])->orderBy('name', 'asc')->get();
        $users = User::orderBy('name', 'asc')->get();

        return view('admin.projects.index', compact('projects', 'classes', 'users'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'project_leader_id' => 'required|exists:users,id',
            'actual_start_date' => 'required|date',
            'week_1_start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:actual_start_date',
            'status' => 'required|string|in:PLANNED,ACTIVE,COMPLETED,ON_HOLD',
            'description' => 'nullable|string'
        ]);

        Project::create($validated);

        return back()->with('success', 'Project baru berhasil diinisiasi.');
    }

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'class_id' => 'required|exists:classes,id',
            'project_leader_id' => 'required|exists:users,id',
            'actual_start_date' => 'required|date',
            'week_1_start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:actual_start_date',
            'status' => 'required|string|in:PLANNED,ACTIVE,COMPLETED,ON_HOLD',
            'description' => 'nullable|string'
        ]);

        $project->update($validated);

        return back()->with('success', 'Data project berhasil diperbarui.');
    }

    public function destroy(Project $project)
    {
        try {
            $project->delete();
            return back()->with('success', 'Project berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Gagal! Project ini sudah memiliki riwayat Anggota, Divisi, atau Laporan.');
        }
    }
}
