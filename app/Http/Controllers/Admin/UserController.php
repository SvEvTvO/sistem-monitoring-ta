<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\SchoolClass;
use App\Models\Project;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // JIKA REQUEST AJAX
        if ($request->ajax() && $request->has('class_id')) {
            $classId = $request->class_id;

            if ($classId === 'unassigned') {
                $project = null;
                $isUnassigned = true;
                $userQuery = User::whereDoesntHave('classMemberships')
                                 ->where('is_admin', false);
            } else {
                $project = Project::where('class_id', $classId)
                                ->with(['leader', 'divisions.members.user'])
                                ->first();
                $isUnassigned = false;
                $userQuery = User::whereHas('classMemberships', function ($q) use ($classId) {
                    $q->where('class_id', $classId);
                })->where('is_admin', false);
            }

            // [OPTIMASI TAHAP 1]: Pindahkan Filter Pencarian ke SQL Engine
            // Menghemat RAM secara drastis dibanding mem-filter Collection di memori PHP
            if ($request->filled('search')) {
                $search = $request->search;
                $userQuery->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('username', 'like', "%{$search}%");
                });
            }

            $usersInClass = $userQuery->orderBy('name', 'asc')->get();

            return view('admin.users.partials.division-structure', compact('project', 'usersInClass', 'isUnassigned'))->render();
        }

        // [OPTIMASI TAHAP 2]: Tambahkan 'academicYear' untuk mencegah N+1 di dropdown Kelas
        $classes = SchoolClass::with('academicYear')->orderBy('name', 'asc')->get();
        $hasUnassigned = User::whereDoesntHave('classMemberships')->where('is_admin', false)->exists();

        return view('admin.users.index', compact('classes', 'hasUnassigned'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8',
            'class_id' => 'nullable|exists:classes,id',
        ]);

        $userData = collect($validated)->except('class_id')->toArray();
        $userData['password'] = Hash::make($userData['password']);
        $userData['email_verified_at'] = now();

        $user = User::create($userData);

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

    public function show(User $user)
    {
        $user->load(['classMemberships.schoolClass.department', 'projectMembers.project', 'projectMembers.division']);

        // [OPTIMASI TAHAP 3]: Eager Load 'projectWeek'
        // Mencegah N+1 saat file Blade mencoba merender "Minggu Ke-X"
        $reports = \App\Models\Report::with('projectWeek')
                                     ->where('author_id', $user->id)
                                     ->orderBy('created_at', 'desc')
                                     ->get();

        return view('admin.users.show', compact('user', 'reports'));
    }
}
