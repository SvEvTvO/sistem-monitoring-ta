<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\ProjectMember;

class MemberController extends Controller
{
    // Tampilkan Halaman Manajemen Anggota
    public function index()
    {
        $user = Auth::user();
        
        // [OPTIMASI TAHAP 1]: Ambil data project/divisi sekali saja untuk menghindari query berulang
        $ledProject = $user->ledProjects()->first();
        $isProjectLeader = $ledProject !== null;
        
        // Jika bukan ketua project, ambil data divisi beserta project-nya (Eager Load)
        $ledDivision = null;
        if (!$isProjectLeader) {
            $ledDivision = $user->ledDivisions()->with('project')->first();
        }

        if (!$isProjectLeader && !$ledDivision) {
            abort(403, 'Akses ditolak. Anda bukan Ketua Project atau Ketua Divisi.');
        }

        $project = $isProjectLeader ? $ledProject : $ledDivision->project;

        $unassignedUsers = collect();
        if ($isProjectLeader) {
            $classId = $project->class_id; 
            
            $unassignedUsers = User::whereHas('classMemberships', function($q) use ($classId) {
                $q->where('class_id', $classId);
            })->whereDoesntHave('projectMembers', function($q) use ($project) {
                $q->where('project_id', $project->id);
            })->where('is_admin', false)
            ->orderBy('name', 'asc') // [OPTIMASI]: Urutkan secara alfabet agar rapi
            ->get();
        }

        // [OPTIMASI TAHAP 2]: Query divisi tidak perlu dibedakan strukturnya, hanya diubah filternya saja
        $divisionsQuery = ProjectDivision::with(['members.user']);
        
        if ($isProjectLeader) {
            $divisions = $divisionsQuery->where('project_id', $project->id)->get();
        } else {
            $divisions = $divisionsQuery->where('id', $ledDivision->id)->get();
        }

        return view('members.index', compact('project', 'isProjectLeader', 'ledDivision', 'unassignedUsers', 'divisions'));
    }

    // Aksi: Buat Divisi Baru
    public function storeDivision(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10', 
            'leader_user_id' => 'required|exists:users,id' 
        ]);
        
        $user = Auth::user();
        
        // [OPTIMASI]: Cukup gunakan select id untuk menghemat alokasi memori
        $project = $user->ledProjects()->select('id')->first();

        if (!$project) abort(403);

        $division = ProjectDivision::create([
            'project_id' => $project->id,
            'name' => $request->name,
            'code' => strtoupper($request->code),
            'leader_user_id' => $request->leader_user_id
        ]);

        ProjectMember::create([
            'user_id' => $request->leader_user_id,
            'project_id' => $project->id,
            'division_id' => $division->id,
            'joined_at' => now(),
        ]);

        return back()->with('success', 'Divisi baru berhasil dibuat beserta Ketuanya.');
    }

    // Aksi: Tetapkan Ketua Divisi
    public function setLeader(Request $request, ProjectDivision $division)
    {
        $request->validate(['user_id' => 'required|exists:users,id']);
        
        $division->update(['leader_user_id' => $request->user_id]);

        return back()->with('success', 'Ketua Divisi berhasil ditetapkan.');
    }

    // Aksi: Masukkan User ke Divisi (Mendukung Multiple Input)
    public function assignMember(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'division_id' => 'required|exists:project_divisions,id'
        ], [
            'user_ids.required' => 'Pilih setidaknya satu anggota untuk dimasukkan.'
        ]);

        // [OPTIMASI TAHAP 3]: Hindari select * jika hanya butuh project_id dan id
        $division = ProjectDivision::select('id', 'project_id')->findOrFail($request->division_id);

        // [OPTIMASI TAHAP 4]: BULK INSERT! 
        // Array dikumpulkan lalu dieksekusi dalam 1 kali query ke database. Jauh lebih cepat dari looping create().
        $now = now();
        $dataToInsert = [];
        
        foreach ($request->user_ids as $userId) {
            $dataToInsert[] = [
                'user_id' => $userId,
                'project_id' => $division->project_id,
                'division_id' => $division->id,
                'joined_at' => $now->toDateString(),
                'is_active' => 1,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }
        
        ProjectMember::insert($dataToInsert); // 1 Query execution!

        return back()->with('success', count($request->user_ids) . ' anggota berhasil ditambahkan ke divisi.');
    }

    // Aksi: Keluarkan User dari Divisi
    public function removeMember(Request $request, ProjectMember $member)
    {
        // [OPTIMASI FATAL N+1]: Wajib load project dan division SEBELUM dicek isProjectLeader!
        $member->load(['project', 'division']);

        $user = Auth::user();
        $isProjectLeader = $member->project->project_leader_id === $user->id;
        $isDivisionLeader = $user->ledDivisions()->where('id', $member->division_id)->exists();

        if (!$isProjectLeader && !$isDivisionLeader) {
            abort(403, 'Anda tidak berhak mengeluarkan anggota ini.');
        }

        // Karena sudah di-load di atas, kita tidak butuh 'ProjectDivision::find()' lagi.
        $division = $member->division;

        if ($division && $division->leader_user_id == $member->user_id) {
            $request->validate([
                'new_leader_id' => 'required|exists:users,id'
            ], [
                'new_leader_id.required' => 'Pilih ketua baru sebelum mengeluarkan ketua saat ini.'
            ]);
            
            $division->update(['leader_user_id' => $request->new_leader_id]);
        }

        $member->delete();

        return back()->with('success', 'Anggota berhasil dikeluarkan dari divisi (Kembali menjadi Unassigned).');
    }

    // Aksi: Lihat Detail Anggota & Riwayat Laporannya
    public function show(ProjectMember $member)
    {
        // [OPTIMASI FATAL N+1]: Pindahkan load ke ATAS sebelum dipanggil di bawahnya!
        $member->load(['user', 'division', 'project']);

        $user = Auth::user();
        
        $isProjectLeader = $member->project->project_leader_id === $user->id;
        $isDivisionLeader = $user->ledDivisions()->where('id', $member->division_id)->exists();

        if (!$isProjectLeader && !$isDivisionLeader) {
            abort(403, 'Akses ditolak. Anda tidak berhak melihat detail anggota ini.');
        }

        // Pengambilan data laporan sudah cukup cepat karena menembak indeks (author_id & division_id)
        $reports = \App\Models\Report::where('author_id', $member->user_id)
                                     ->where('division_id', $member->division_id)
                                     ->orderBy('created_at', 'desc')
                                     ->get();

        return view('members.show', compact('member', 'reports', 'isProjectLeader'));
    }
}
