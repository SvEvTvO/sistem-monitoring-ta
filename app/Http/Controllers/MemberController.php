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
        
        $isProjectLeader = $user->ledProjects()->exists();
        $ledDivision = $user->ledDivisions()->first();

        if (!$isProjectLeader && !$ledDivision) {
            abort(403, 'Akses ditolak. Anda bukan Ketua Project atau Ketua Divisi.');
        }

        $project = $isProjectLeader ? $user->ledProjects()->first() : $ledDivision->project;

        // FIX LOGIKA FATAL: 
        // Ketua Project HANYA boleh melihat siswa yang:
        // 1. Berada di Rombel Kelas yang sama dengan Project ini.
        // 2. Belum dimasukkan ke dalam project_members di project ini.
        // 3. Bukan Admin.
        $unassignedUsers = collect();
        if ($isProjectLeader) {
            $classId = $project->class_id; // Ambil ID Kelas dari project
            
            $unassignedUsers = User::whereHas('classMemberships', function($q) use ($classId) {
                $q->where('class_id', $classId); // Syarat 1: Harus di kelas yang sama!
            })->whereDoesntHave('projectMembers', function($q) use ($project) {
                $q->where('project_id', $project->id); // Syarat 2: Belum masuk ke project ini
            })->where('is_admin', false) // Syarat 3: Bukan admin
            ->get();
        }

        if ($isProjectLeader) {
            $divisions = ProjectDivision::where('project_id', $project->id)
                                        ->with(['members.user']) 
                                        ->get();
        } else {
            $divisions = ProjectDivision::where('id', $ledDivision->id)
                                        ->with(['members.user'])
                                        ->get();
        }

        return view('members.index', compact('project', 'isProjectLeader', 'ledDivision', 'unassignedUsers', 'divisions'));
    }

    // Aksi: Buat Divisi Baru
    public function storeDivision(Request $request)
    {
        // 1. Validasi: Nama divisi, KODE, & Ketua wajib diisi
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:10', // <-- Tambahan validasi kode
            'leader_user_id' => 'required|exists:users,id' 
        ]);
        
        $user = Auth::user();
        $project = $user->ledProjects()->first();

        if (!$project) abort(403);

        // 2. Buat divisi beserta kodenya (diubah jadi huruf besar otomatis)
        $division = ProjectDivision::create([
            'project_id' => $project->id,
            'name' => $request->name,
            'code' => strtoupper($request->code), // <-- Simpan kode divisi
            'leader_user_id' => $request->leader_user_id
        ]);

        // 3. Wajib: Masukkan ketua ke tabel anggota
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
        // Validasi diubah menjadi array (user_ids)
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'division_id' => 'required|exists:project_divisions,id'
        ], [
            'user_ids.required' => 'Pilih setidaknya satu anggota untuk dimasukkan.'
        ]);

        $division = ProjectDivision::findOrFail($request->division_id);

        // Looping untuk menyimpan setiap user yang dipilih
        foreach ($request->user_ids as $userId) {
            ProjectMember::create([
                'user_id' => $userId,
                'project_id' => $division->project_id,
                'division_id' => $division->id,
                'joined_at' => now(),
            ]);
        }

        return back()->with('success', count($request->user_ids) . ' anggota berhasil ditambahkan ke divisi.');
    }

    // Aksi: Keluarkan User dari Divisi
    public function removeMember(Request $request, ProjectMember $member)
    {
        $user = Auth::user();
        $isProjectLeader = $member->project->project_leader_id === $user->id;
        $isDivisionLeader = $user->ledDivisions()->where('id', $member->division_id)->exists();

        if (!$isProjectLeader && !$isDivisionLeader) {
            abort(403, 'Anda tidak berhak mengeluarkan anggota ini.');
        }

        $division = ProjectDivision::find($member->division_id);

        // Jika yang dikeluarkan adalah KETUA divisi
        if ($division && $division->leader_user_id == $member->user_id) {
            // Wajibkan memilih ketua pengganti
            $request->validate([
                'new_leader_id' => 'required|exists:users,id'
            ], [
                'new_leader_id.required' => 'Pilih ketua baru sebelum mengeluarkan ketua saat ini.'
            ]);
            
            // Pindahkan takhta ketua ke orang baru
            $division->update(['leader_user_id' => $request->new_leader_id]);
        }

        // Setelah aman, hapus dari keanggotaan
        $member->delete();

        return back()->with('success', 'Anggota berhasil dikeluarkan dari divisi (Kembali menjadi Unassigned).');
    }


    // Aksi: Lihat Detail Anggota & Riwayat Laporannya
    public function show(ProjectMember $member)
    {
        $user = Auth::user();
        
        // Cek hak akses: Hanya Ketua Project atau Ketua Divisi terkait yang boleh melihat
        $isProjectLeader = $member->project->project_leader_id === $user->id;
        $isDivisionLeader = $user->ledDivisions()->where('id', $member->division_id)->exists();

        if (!$isProjectLeader && !$isDivisionLeader) {
            abort(403, 'Akses ditolak. Anda tidak berhak melihat detail anggota ini.');
        }

        $member->load(['user', 'division']);

        // Ambil riwayat laporan anggota ini khusus di divisi tersebut
        $reports = \App\Models\Report::where('author_id', $member->user_id)
                                     ->where('division_id', $member->division_id)
                                     ->orderBy('created_at', 'desc')
                                     ->get();

        return view('members.show', compact('member', 'reports', 'isProjectLeader'));
    }
}