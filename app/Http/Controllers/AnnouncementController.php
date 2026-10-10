<?php

namespace App\Http\Controllers;

use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AnnouncementController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // [OPTIMASI]: Eager load project & division untuk mencegah N+1
        $membership = $user->projectMembers()->with(['project', 'division'])->first();
        if (!$membership) return redirect()->route('dashboard');

        $project = $membership->project;
        $divisionId = $membership->division_id;

        $isProjectLeader = $project->project_leader_id === $user->id;
        $ledDivision = $user->ledDivisions()->where('project_id', $project->id)->first();
        $isDivisionLeader = $ledDivision ? true : false;

        $canCreate = $isProjectLeader || $isDivisionLeader;

        // [OPTIMASI]: with(['creator', 'division']) sudah sangat tepat untuk tabel!
        $query = Announcement::where('project_id', $project->id)
            ->where(function($q) use ($divisionId) {
                $q->where('audience_type', 'ALL_PROJECT')
                  ->orWhere('division_id', $divisionId);
            })
            ->with(['creator', 'division']);

        // Fitur Pencarian (Aman & Optimal)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('content', 'like', '%' . $request->search . '%');
            });
        }

        $announcements = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();

        return view('announcements.index', compact('announcements', 'isProjectLeader', 'ledDivision', 'canCreate', 'project'));
    }

    public function create()
    {
        $user = Auth::user();

        // [OPTIMASI]: Mencegah N+1 saat memanggil $membership->project di baris bawahnya
        $membership = $user->projectMembers()->with('project')->first();
        if (!$membership) return redirect()->route('dashboard');

        $project = $membership->project;
        $isProjectLeader = $project->project_leader_id === $user->id;
        $ledDivision = $user->ledDivisions()->where('project_id', $project->id)->first();
        $canCreate = $isProjectLeader || $ledDivision;

        if (!$canCreate) abort(403, 'Akses ditolak. Hanya Ketua Project atau Ketua Divisi yang dapat membuat pengumuman.');

        return view('announcements.create', compact('project', 'isProjectLeader', 'ledDivision'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title'      => 'required|string|max:200',
            'content'    => 'required|string',
        ]);

        $user = Auth::user();

        // [OPTIMASI]: Menggunakan select untuk meringankan beban memory
        $project = \App\Models\Project::select('id', 'project_leader_id')->findOrFail($validated['project_id']);

        $isProjectLeader = $project->project_leader_id === $user->id;
        $ledDivision = $user->ledDivisions()->where('project_id', $project->id)->first();

        // Otomatisasi target audiens berdasarkan jabatan
        $audienceType = 'ALL_PROJECT';
        $divisionId = null;

        if (!$isProjectLeader && $ledDivision) {
            $audienceType = 'DIVISION';
            $divisionId = $ledDivision->id;
        }

        Announcement::create([
            'project_id'    => $validated['project_id'],
            'title'         => $validated['title'],
            'content'       => $validated['content'],
            'audience_type' => $audienceType,
            'division_id'   => $divisionId,
            'created_by'    => $user->id,
            'published_at'  => now(),
        ]);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil dipublikasikan.');
    }

    public function show(Announcement $announcement)
    {
        // [OPTIMASI FATAL N+1]: Route binding ($announcement) datang tanpa relasi.
        // Wajib me-load project & division & creator agar blade/logika tidak memanggil query ulang!
        $announcement->load(['project', 'division', 'creator']);

        $user = Auth::user();
        $membership = $user->projectMembers()->where('project_id', $announcement->project_id)->first();
        if (!$membership) abort(403);

        // Karena sudah di-load di atas, baris di bawah ini TIDAK AKAN melakukan query ke DB lagi (Aman dari N+1)
        $isProjectLeader = $announcement->project->project_leader_id === $user->id;
        $ledDivision = $user->ledDivisions()->where('project_id', $announcement->project_id)->first();

        // Cek visibilitas: Anggota divisi lain tidak boleh melihat pengumuman divisi khusus
        if (!$isProjectLeader && $announcement->audience_type === 'DIVISION') {
            if ($membership->division_id !== $announcement->division_id) {
                abort(403, 'Akses ditolak. Pengumuman ini khusus untuk divisi lain.');
            }
        }

        // Cek Hak Edit
        $canEdit = false;
        if ($isProjectLeader) {
            $canEdit = true;
        } elseif ($ledDivision && $announcement->division_id === $ledDivision->id) {
            $canEdit = true;
        }

        return view('announcements.show', compact('announcement', 'canEdit', 'isProjectLeader'));
    }

    public function edit(Announcement $announcement)
    {
        // [OPTIMASI FATAL N+1]: Wajib load project!
        $announcement->load('project');

        $user = Auth::user();
        $isProjectLeader = $announcement->project->project_leader_id === $user->id;
        $ledDivision = $user->ledDivisions()->where('project_id', $announcement->project_id)->first();

        $canEdit = false;
        if ($isProjectLeader) {
            $canEdit = true;
        } elseif ($ledDivision && $announcement->division_id === $ledDivision->id) {
            $canEdit = true;
        }

        if (!$canEdit) abort(403, 'Anda tidak memiliki hak untuk mengedit pengumuman ini.');

        return view('announcements.edit', compact('announcement', 'isProjectLeader', 'ledDivision'));
    }

    public function update(Request $request, Announcement $announcement)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:200',
            'content' => 'required|string',
        ]);

        // [OPTIMASI FATAL N+1]: Wajib load project agar pengecekan relasi cepat
        $announcement->load('project');

        $user = Auth::user();
        $isProjectLeader = $announcement->project->project_leader_id === $user->id;
        $ledDivision = $user->ledDivisions()->where('project_id', $announcement->project_id)->first();

        $canEdit = false;
        if ($isProjectLeader || ($ledDivision && $announcement->division_id === $ledDivision->id)) {
            $canEdit = true;
        }

        if (!$canEdit) abort(403);

        $announcement->update($validated);

        return redirect()->route('announcements.index')->with('success', 'Pengumuman berhasil diperbarui.');
    }
}
