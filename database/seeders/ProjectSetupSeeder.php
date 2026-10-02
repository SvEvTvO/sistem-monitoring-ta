<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Department;
use App\Models\Level;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Project;

class ProjectSetupSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $pass = Hash::make('password');

        // ==========================================
        // 1. BOOTSTRAP DATA MASTER (Sesuai Skema Asli)
        // ==========================================
        $dept = Department::create(['name' => 'Rekayasa Perangkat Lunak', 'code' => 'RPL']);
        $level = Level::create(['name' => '12', 'sort_order' => 3]);
        $year = AcademicYear::create([
            'name' => '2026/2027', 
            'start_date' => '2026-07-01', 
            'end_date' => '2027-06-30', 
            'is_active' => true
        ]);
        $class = SchoolClass::create([
            'department_id' => $dept->id, 
            'level_id' => $level->id, 
            'academic_year_id' => $year->id, 
            'name' => 'XII RPL 1'
        ]);

        // ==========================================
        // 2. BUAT AKUN KETUA PROJECT (Miftah)
        // ==========================================
        $leader = User::create([
            'name' => 'Moch Miftahul Khoironi (Ketua Project)',
            'username' => 'miftah',
            'email' => 'miftah@monitoring.com',
            'password' => $pass,
            'remember_token' => Str::random(10),
        ]);

        // ==========================================
        // 3. BUAT PROJECT (Dengan parameter waktu lengkap)
        // ==========================================
        Project::create([
            'class_id' => $class->id,
            'name' => 'Pengembangan Sistem Monitoring TA',
            // Memasukkan kolom wajib sesuai DatabaseSeeder
            'actual_start_date' => $now->copy()->startOfWeek(),
            'week_1_start_date' => $now->copy()->startOfWeek(),
            'end_date' => $now->copy()->addMonths(3),
            'project_leader_id' => $leader->id,
            'status' => 'ACTIVE'
        ]);

        // ==========================================
        // 4. BUAT 15 ANGGOTA "NGANGGUR" (Kolam Unassigned)
        // ==========================================
        $unassignedUsers = [
            'Eka Saputri', 'Nina Zatulini', 'Okan Kornelius', 'Putra Siregar',
            'Qori Akbar', 'Rey Mbayang', 'Fadli Zon', 'Gita Gutawa',
            'Hasan Sadikin', 'Indra Bekti', 'Joko Anwar', 'Kirana Larasati',
            'Lesti Kejora', 'Maya Septha', 'Nirina Zubir'
        ];

        foreach ($unassignedUsers as $index => $name) {
            $firstName = strtolower(explode(' ', $name)[0]);
            
            User::create([
                'name' => $name,
                // Tambahkan index agar username dijamin unik
                'username' => $firstName . $index,
                'email' => $firstName . '@monitoring.com',
                'password' => $pass,
                'remember_token' => Str::random(10),
            ]);
        }
    }
}