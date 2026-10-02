<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Department;
use App\Models\Level;
use App\Models\AcademicYear;
use App\Models\SchoolClass;
use App\Models\Project;
use App\Models\ProjectDivision;
use App\Models\ProjectMember;
use App\Models\ProjectWeek;
use App\Models\ReportEvaluationLabel;
use App\Models\Report;
use App\Models\ProjectTarget;
use App\Models\Announcement;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();
        $pass = Hash::make('password');

        // ==========================================
        // 1. DATA MASTER & LABEL EVALUASI
        // ==========================================
        $labels = [
            ReportEvaluationLabel::create(['name' => 'SANGAT BAIK', 'sort_order' => 1]),
            ReportEvaluationLabel::create(['name' => 'BAIK', 'sort_order' => 2]),
            ReportEvaluationLabel::create(['name' => 'PERLU PERBAIKAN', 'sort_order' => 3]),
            ReportEvaluationLabel::create(['name' => 'KURANG', 'sort_order' => 4]),
        ];

        $dept = Department::create(['name' => 'Rekayasa Perangkat Lunak', 'code' => 'RPL']);
        $level = Level::create(['name' => '12', 'sort_order' => 3]);
        $year = AcademicYear::create(['name' => '2026/2027', 'start_date' => '2026-07-01', 'end_date' => '2027-06-30', 'is_active' => true]);
        $class = SchoolClass::create(['department_id' => $dept->id, 'level_id' => $level->id, 'academic_year_id' => $year->id, 'name' => 'XII RPL 1']);

        // ==========================================
        // 2. GENERATE 20 USERS (1 PM, 5 Leads, 14 Members)
        // ==========================================
        $users = [];
        $names = [
            'Moch Miftahul Khoironi', 'Budi Santoso', 'Citra Kirana', 'Deni Pratama', 'Eka Saputri', 
            'Fajar Hidayat', 'Gilang Ramadhan', 'Hani Amalia', 'Iqbal Tawakal', 'Joko Susilo', 
            'Kiki Fatmala', 'Lina Marlina', 'Mira Lesmana', 'Nina Zatulini', 'Okan Kornelius', 
            'Putra Siregar', 'Qori Akbar', 'Rara Lida', 'Sisil Priscillia', 'Tomi Soeharto'
        ];

        foreach ($names as $index => $name) {
            $roleStr = '';
            if ($index == 0) $roleStr = ' (Ketua Project)';
            elseif ($index >= 1 && $index <= 5) $roleStr = ' (Ketua Divisi)';
            
            $users[] = User::create([
                'name' => $name . $roleStr,
                'username' => strtolower(explode(' ', $name)[0]) . $index,
                'email' => strtolower(explode(' ', $name)[0]) . '@monitoring.com',
                'password' => $pass,
                'remember_token' => Str::random(10),
            ]);
        }

        // ==========================================
        // 3. SETUP PROJECT & 5 DIVISI
        // ==========================================
        $project = Project::create([
            'class_id' => $class->id,
            'name' => 'Sistem Informasi Skala Besar (SISB)',
            'actual_start_date' => $now->copy()->subWeeks(3)->startOfWeek(),
            'week_1_start_date' => $now->copy()->subWeeks(3)->startOfWeek(),
            'end_date' => $now->copy()->addMonths(3),
            'project_leader_id' => $users[0]->id, // Miftah
            'status' => 'ACTIVE'
        ]);

        $divisions = [
            ProjectDivision::create(['project_id' => $project->id, 'name' => 'Web Dev', 'code' => 'WEB', 'leader_user_id' => $users[1]->id, 'weight' => 1.5]),
            ProjectDivision::create(['project_id' => $project->id, 'name' => 'Mobile Dev', 'code' => 'MOB', 'leader_user_id' => $users[2]->id, 'weight' => 1.5]),
            ProjectDivision::create(['project_id' => $project->id, 'name' => 'Desktop Dev', 'code' => 'DSK', 'leader_user_id' => $users[3]->id, 'weight' => 1.0]),
            ProjectDivision::create(['project_id' => $project->id, 'name' => 'UI/UX Design', 'code' => 'DES', 'leader_user_id' => $users[4]->id, 'weight' => 1.0]),
            ProjectDivision::create(['project_id' => $project->id, 'name' => 'QA & Testing', 'code' => 'QAT', 'leader_user_id' => $users[5]->id, 'weight' => 1.0]),
        ];

        // ==========================================
        // 4. DISTRIBUSI MEMBER KE DIVISI (Total 20)
        // ==========================================
        $divisionMembers = [
            0 => [$users[0], $users[1], $users[6], $users[7]],       // Web (4 Orang)
            1 => [$users[2], $users[8], $users[9], $users[10]],      // Mobile (4 Orang)
            2 => [$users[3], $users[11], $users[12]],                // Desktop (3 Orang)
            3 => [$users[4], $users[13], $users[14], $users[15], $users[16]], // UI/UX (5 Orang)
            4 => [$users[5], $users[17], $users[18], $users[19]],    // QA (4 Orang)
        ];

        $joinDate = $now->copy()->subWeeks(3);
        foreach ($divisionMembers as $divIndex => $members) {
            foreach ($members as $member) {
                ProjectMember::create([
                    'project_id' => $project->id,
                    'division_id' => $divisions[$divIndex]->id,
                    'user_id' => $member->id,
                    'joined_at' => $joinDate
                ]);
            }
        }

        // ==========================================
        // 5. GENERATE 4 SIKLUS MINGGU (1 s/d 4)
        // ==========================================
        $weeks = [];
        for ($i = 1; $i <= 4; $i++) {
            $weeks[] = ProjectWeek::create([
                'project_id' => $project->id,
                'week_number' => $i,
                'week_start' => $now->copy()->subWeeks(4 - $i)->startOfWeek(),
                'week_end' => $now->copy()->subWeeks(4 - $i)->endOfWeek(),
                'report_open_at' => $now->copy()->subWeeks(4 - $i)->startOfWeek(), 
                'report_close_at' => $now->copy()->subWeeks(4 - $i)->endOfWeek()->endOfDay(),
                'revision_close_at' => $now->copy()->subWeeks(4 - $i)->endOfWeek()->addDays(4)->endOfDay(),
            ]);
        }

        // ==========================================
        // 6. GENERATE LAPORAN MINGGU LALU (W1, W2, W3)
        // ==========================================
        // Skenario Progress Divisi (Naik setiap minggu)
        $progressScenario = [
            0 => [20, 45, 75], // Web
            1 => [15, 40, 65], // Mobile
            2 => [10, 25, 40], // Desktop
            3 => [35, 65, 95], // UI/UX (Paling Cepat)
            4 => [5, 15, 30],  // QA (Paling Lambat)
        ];

        for ($w = 0; $w < 3; $w++) { // Loop Minggu 1 - 3
            foreach ($divisionMembers as $divIndex => $members) {
                $div = $divisions[$divIndex];
                
                // Laporan Personal Setiap Member (APPROVED)
                foreach ($members as $member) {
                    $reviewerId = ($member->id == $users[0]->id) ? $users[1]->id : $div->leader_user_id;
                    
                    Report::create([
                        'project_id' => $project->id, 'project_week_id' => $weeks[$w]->id,
                        'author_id' => $member->id, 'division_id' => $div->id,
                        'type' => 'PERSONAL', 'title' => "Pengerjaan Task M" . ($w+1) . " - " . $member->name,
                        'work_done' => "Menyelesaikan assigned task minggu ke-" . ($w+1),
                        'next_plan' => "Melanjutkan ke tahap berikutnya.",
                        'status' => 'APPROVED',
                        'reviewed_by' => $reviewerId, 'decided_by' => $reviewerId,
                        'evaluation_label_id' => $labels[rand(0, 1)]->id, 
                        'review_comment' => "Bagus, pertahankan ritme kerjanya."
                    ]);
                }

                // Laporan Divisi dari Ketua Divisi (APPROVED)
                Report::create([
                    'project_id' => $project->id, 'project_week_id' => $weeks[$w]->id,
                    'author_id' => $div->leader_user_id, 'division_id' => $div->id,
                    'type' => 'DIVISION', 'title' => "Laporan Progress " . $div->name . " M" . ($w+1),
                    'work_done' => "Sprint minggu " . ($w+1) . " berjalan lancar.",
                    'next_plan' => "Persiapan sprint selanjutnya.",
                    'progress_percentage' => $progressScenario[$divIndex][$w],
                    'status' => 'APPROVED',
                    'reviewed_by' => $users[0]->id, 'decided_by' => $users[0]->id,
                    'evaluation_label_id' => $labels[rand(0, 1)]->id,
                    'review_comment' => "Progress tercatat. Good job tim " . $div->name
                ]);
            }
        }

        // ==========================================
        // 7. GENERATE LAPORAN MINGGU INI (W4 - Berjalan)
        // ==========================================
        // Beberapa anggota sudah mengumpulkan laporan (Status SUBMITTED)
        $earlyBirds = [$users[6], $users[8], $users[13], $users[17]]; 
        foreach ($earlyBirds as $bird) {
            $memberDiv = ProjectMember::where('user_id', $bird->id)->first();
            Report::create([
                'project_id' => $project->id, 'project_week_id' => $weeks[3]->id,
                'author_id' => $bird->id, 'division_id' => $memberDiv->division_id,
                'type' => 'PERSONAL', 'title' => "Laporan Awal M4",
                'work_done' => "Sudah mulai mengerjakan task minggu ini.",
                'next_plan' => "Finishing sebelum weekend.",
                'status' => 'SUBMITTED' // Menunggu di-review Ketua Divisi
            ]);
        }
        
        // Satu Ketua Divisi mengumpulkan Laporan Divisi M4 (Status SUBMITTED)
        Report::create([
            'project_id' => $project->id, 'project_week_id' => $weeks[3]->id,
            'author_id' => $users[4]->id, 'division_id' => $divisions[3]->id, // UI/UX
            'type' => 'DIVISION', 'title' => "Laporan Akhir UI/UX (Sprint 4)",
            'work_done' => "Semua desain selesai 100%.",
            'next_plan' => "Handover ke tim Frontend.",
            'progress_percentage' => 100,
            'status' => 'SUBMITTED' // Menunggu di-review Miftah
        ]);

        // ==========================================
        // 8. DATA TARGETS
        // ==========================================
        $targets = [
            ['title' => 'Riset & Wireframing', 'start' => 21, 'end' => 14, 'done' => 14], // Selesai
            ['title' => 'Slicing UI & Asset', 'start' => 14, 'end' => 7, 'done' => 7], // Selesai
            ['title' => 'Setup Database & API', 'start' => 7, 'end' => 0, 'done' => null], // Deadline Hari Ini!
            ['title' => 'Integrasi Frontend', 'start' => 0, 'end' => -7, 'done' => null], // Deadline mgg depan
            ['title' => 'UAT & Bug Fixing', 'start' => -7, 'end' => -14, 'done' => null], // Masih lama
        ];

        foreach ($targets as $t) {
            ProjectTarget::create([
                'project_id' => $project->id,
                'title' => $t['title'],
                'start_date' => $now->copy()->subDays($t['start']),
                'deadline' => $now->copy()->subDays($t['end']),
                'completed_at' => $t['done'] ? $now->copy()->subDays($t['done']) : null,
                'completed_by' => $t['done'] ? $users[0]->id : null,
                'created_by' => $users[0]->id
            ]);
        }

        // ==========================================
        // 9. DATA PENGUMUMAN
        // ==========================================
        Announcement::create([
            'project_id' => $project->id, 'created_by' => $users[0]->id,
            'title' => 'Kick-off Project SISB', 'content' => "Selamat datang semua di project ini. Mari kita kerjakan dengan maksimal!",
            'audience_type' => 'ALL_PROJECT', 'published_at' => $now->copy()->subWeeks(3)
        ]);

        Announcement::create([
            'project_id' => $project->id, 'created_by' => $users[0]->id,
            'title' => 'URGENT: Perubahan Struktur Database', 'content' => "Kepada seluruh tim Web dan Mobile, tolong hentikan fetch data sementara, sedang ada migrasi DB.",
            'audience_type' => 'ALL_PROJECT', 'published_at' => $now->copy()->subDays(2)
        ]);

        Announcement::create([
            'project_id' => $project->id, 'created_by' => $users[4]->id, // Ketua UI/UX
            'title' => 'Revisi Aset Warna', 'content' => "Tim desainer, tolong gunakan palet hijau neon terbaru ya untuk tombol utama.",
            'audience_type' => 'DIVISION', 'division_id' => $divisions[3]->id, 'published_at' => $now->copy()->subDays(1)
        ]);
    }
}