<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AcademicYear;
use App\Models\Department;
use App\Models\Level;
use App\Models\SchoolClass;

class MasterDataController extends Controller
{
    public function index()
    {
        // [OPTIMASI TAHAP 1]: Tambahkan withCount() untuk mencegah N+1 di halaman Blade
        // Admin bisa langsung menampilkan jumlah kelas yang memakai Tahun Ajaran/Jurusan/Level tersebut
        // Pastikan Model terkait memiliki fungsi relasi classes()
        $academicYears = AcademicYear::withCount('classes')->orderBy('start_date', 'desc')->get();
        $departments = Department::withCount('classes')->orderBy('name', 'asc')->get();
        $levels = Level::withCount('classes')->orderBy('sort_order', 'asc')->get();

        // [OPTIMASI TAHAP 2]: Tambahkan penghitungan jumlah Siswa & Project per Rombel
        // Pastikan Model SchoolClass memiliki relasi classMemberships() & projects()
        $classes = SchoolClass::with(['academicYear', 'department', 'level'])
                              ->withCount(['classMemberships', 'projects'])
                              ->orderBy('name', 'asc')
                              ->get();

        return view('admin.master-data.index', compact('academicYears', 'departments', 'levels', 'classes'));
    }

    // ==========================================
    // FUNGSI CREATE (TAMBAH DATA)
    // ==========================================

    public function storeAcademicYear(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $validated['is_active'] = $request->has('is_active'); // Checkbox boolean
        AcademicYear::create($validated);

        return back()->with('success', 'Tahun Ajaran baru berhasil ditambahkan.');
    }

    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:departments,code',
            'name' => 'required|string|max:100',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        Department::create($validated);

        return back()->with('success', 'Jurusan / Departemen baru berhasil ditambahkan.');
    }

    public function storeLevel(Request $request)
    {
        $validated = $request->validate([
            'sort_order' => 'required|integer|min:1',
            'name' => 'required|string|max:10'
        ]);

        Level::create($validated);
        return back()->with('success', 'Data Kelas (10, 11, 12) baru berhasil ditambahkan.');
    }

    public function storeClass(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:classes,name',
            'academic_year_id' => 'required|exists:academic_years,id',
            'department_id' => 'required|exists:departments,id',
            'level_id' => 'required|exists:levels,id',
        ]);

        SchoolClass::create($validated);
        return back()->with('success', 'Rombongan Belajar (Rombel) baru berhasil ditambahkan.');
    }

    // ==========================================
    // FUNGSI UPDATE (EDIT DATA)
    // ==========================================

    public function updateAcademicYear(Request $request, AcademicYear $academicYear)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);
        
        $validated['is_active'] = $request->has('is_active');
        $academicYear->update($validated);
        
        return back()->with('success', 'Tahun Ajaran berhasil diperbarui.');
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:departments,code,' . $department->id,
            'name' => 'required|string|max:100',
        ]);
        
        $validated['code'] = strtoupper($validated['code']);
        $department->update($validated);
        
        return back()->with('success', 'Jurusan berhasil diperbarui.');
    }

    public function updateLevel(Request $request, Level $level)
    {
        $validated = $request->validate([
            'sort_order' => 'required|integer|min:1', 
            'name' => 'required|string|max:10'
        ]);
        
        $level->update($validated);
        return back()->with('success', 'Data Tingkat Kelas berhasil diperbarui.');
    }

    public function updateClass(Request $request, SchoolClass $schoolClass)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:classes,name,' . $schoolClass->id,
            'academic_year_id' => 'required|exists:academic_years,id',
            'department_id' => 'required|exists:departments,id',
            'level_id' => 'required|exists:levels,id',
        ]);

        $schoolClass->update($validated);
        return back()->with('success', 'Rombongan Belajar (Rombel) berhasil diperbarui.');
    }

    // ==========================================
    // FUNGSI DESTROY (HAPUS DATA) DENGAN PROTEKSI
    // ==========================================

    public function destroyAcademicYear(AcademicYear $academicYear)
    {
        try {
            $academicYear->delete();
            return back()->with('success', 'Tahun Ajaran berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Gagal! Tahun Ajaran ini sedang digunakan oleh Kelas/Project aktif.');
        }
    }

    public function destroyDepartment(Department $department)
    {
        try {
            $department->delete();
            return back()->with('success', 'Jurusan berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Gagal! Jurusan ini masih memiliki Kelas yang terdaftar.');
        }
    }

    public function destroyLevel(Level $level)
    {
        try {
            $level->delete();
            return back()->with('success', 'Level berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Gagal! Level ini sedang digunakan.');
        }
    }

    public function destroyClass(SchoolClass $schoolClass)
    {
        try {
            $schoolClass->delete();
            return back()->with('success', 'Kelas berhasil dihapus.');
        } catch (\Illuminate\Database\QueryException $e) {
            return back()->with('error', 'Gagal! Kelas ini sudah memiliki Project atau Siswa yang terikat.');
        }
    }
}
