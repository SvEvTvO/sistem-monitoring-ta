<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB; // KUNCI: Untuk memasukkan user ke tabel class_memberships

class UserController extends Controller
{
    public function index(Request $request)
    {
        // JIKA REQUEST AJAX
        if ($request->ajax() && $request->has('class_id')) {
            $classId = $request->class_id;

            if ($classId === 'unassigned') {
                // MODE "BELUM PUNYA KELAS": project sengaja TIDAK diambil →
                // partial menyembunyikan seluruh blok project & hanya menampilkan anggota tanpa kelas.
                $project = null;
                $isUnassigned = true;
                $usersInClass = User::whereDoesntHave('classMemberships')
                                    ->where('is_admin', false)
                                    ->orderBy('name', 'asc')
                                    ->get();
            } else {
                $project = Project::where('class_id', $classId)
                                ->with(['leader', 'divisions.members.user'])
                                ->first();
                $isUnassigned = false;
                $usersInClass = User::whereHas('classMemberships', function ($q) use ($classId) {
                    $q->where('class_id', $classId);
                })->where('is_admin', false)->orderBy('name', 'asc')->get();
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $usersInClass = $usersInClass->filter(function($user) use ($search) {
                    return stripos($user->name, $search) !== false
                        || stripos($user->email, $search) !== false
                        || stripos($user->username, $search) !== false;
                });
            }

            return view('admin.users.partials.division-structure', compact('project', 'usersInClass', 'isUnassigned'))->render();
        }

        // JIKA REQUEST BIASA — tidak berubah
        $classes = SchoolClass::orderBy('name', 'asc')->get();
        $hasUnassigned = User::whereDoesntHave('classMemberships')->where('is_admin', false)->exists();

        return view('admin.users.index', compact('classes', 'hasUnassigned'));
    }

    public function store(Request $request)
    {
        // 1. Validasi input, termasuk class_id
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'class_id' => 'nullable|exists:classes,id', // <-- Tambahan validasi Kelas
        ]);

        // 2. Buat User baru (pisahkan class_id agar tidak ikut tersimpan ke tabel users)
        $userData = collect($validated)->except('class_id')->toArray();
        $userData['password'] = Hash::make($userData['password']);
        $userData['email_verified_at'] = now();

        $user = User::create($userData);

        // 3. JIKA Admin memilih kelas, otomatis daftarkan siswa tersebut ke class_memberships!
        if (!empty($validated['class_id'])) {
            DB::table('class_memberships')->insert([
                'class_id'   => $validated['class_id'],
                'user_id'    => $user->id,
                'joined_at'  => now()->toDateString(),
                'is_active'  => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return back()->with('success', 'Akun pengguna baru berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);
        return back()->with('success', 'Data akun berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        try {
            if ($user->id === auth()->id()) {
                return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
            }
            $user->delete();
            return back()->with('success', 'Akun pengguna berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Gagal! Pengguna ini sudah terikat dengan aktivitas Project.');
        }
    }

    // Aksi: Lihat Detail Lengkap Pengguna (Khusus Admin)
    public function show(User $user)
    {
        // Load relasi Rombel, Project, dan Divisi pengguna ini
        $user->load(['classMemberships.schoolClass.department', 'projectMembers.project', 'projectMembers.division']);
        
        // Tarik seluruh riwayat laporan yang pernah ditulis user ini
        $reports = \App\Models\Report::where('author_id', $user->id)
                                     ->orderBy('created_at', 'desc')
                                     ->get();

        return view('admin.users.show', compact('user', 'reports'));
    }
}
