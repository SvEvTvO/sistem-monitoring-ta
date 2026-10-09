<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
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
        DB::transaction(function () {
            $now  = Carbon::now();
            $pass = Hash::make('password');
            $faker = \Faker\Factory::create('id_ID');

            // ==========================================
            // 0. 3 AKUN ADMIN
            // ==========================================
            $this->createAdmins($pass, $now);

            // ==========================================
            // 1. MASTER DATA
            // ==========================================
            $dept = Department::create([
                'name' => 'Rekayasa Perangkat Lunak',
                'code' => 'RPL',
            ]);

            $level = Level::create([
                'name'       => '12',
                'sort_order' => 3,
            ]);

            $year = AcademicYear::create([
                'name'       => '2026/2027',
                'start_date' => '2026-07-01',
                'end_date'   => '2027-06-30',
                'is_active'  => true,
            ]);

            // ==========================================
            // 2. EVALUATION LABELS (untuk relasi reports)
            // ==========================================
            $this->createEvaluationLabels($now);

            // ==========================================
            // 3. 3 KELAS + 3 PROJECT
            // ==========================================
            $classProjects = [
                [
                    'class_name'   => 'XII RPL 1',
                    'class_code'   => 'XII-RPL-1',
                    'project_name' => 'Sistem Monitoring Tugas Akhir',
                    'project_slug' => 'monitoring-ta',
                    'divisions'    => [
                        ['name' => 'Frontend', 'code' => 'FE'],
                        ['name' => 'Backend',  'code' => 'BE'],
                        ['name' => 'UI/UX',    'code' => 'UX'],
                    ],
                ],
                [
                    'class_name'   => 'XII RPL 2',
                    'class_code'   => 'XII-RPL-2',
                    'project_name' => 'Aplikasi Absensi QR Code',
                    'project_slug' => 'absensi-qr',
                    'divisions'    => [
                        ['name' => 'Mobile',            'code' => 'MB'],
                        ['name' => 'Web Admin',         'code' => 'WA'],
                        ['name' => 'Quality Assurance', 'code' => 'QA'],
                    ],
                ],
                [
                    'class_name'   => 'XII RPL 3',
                    'class_code'   => 'XII-RPL-3',
                    'project_name' => 'E-Commerce Sekolah',
                    'project_slug' => 'ecommerce-sekolah',
                    'divisions'    => [
                        ['name' => 'Product',     'code' => 'PR'],
                        ['name' => 'Engineering', 'code' => 'EN'],
                        ['name' => 'Marketing',   'code' => 'MK'],
                    ],
                ],
            ];

            $createdProjects = [];

            foreach ($classProjects as $cp) {

                // ---- Buat kelas ----
                $class = SchoolClass::create([
                    'department_id'    => $dept->id,
                    'level_id'         => $level->id,
                    'academic_year_id' => $year->id,
                    'name'             => $cp['class_name'],
                    'code'             => $cp['class_code'],
                ]);

                // ---- Ketua project ----
                $projectLeader = $this->createStudentUser(
                    $faker,
                    $cp['project_slug'] . ' project leader',
                    $pass,
                    $now
                );
                $this->insertClassMembership($class->id, $projectLeader->id, $now);

                // ---- Buat project ----
                $project = Project::create([
                    'class_id'           => $class->id,
                    'name'               => $cp['project_name'],
                    'description'        => "Project {$cp['project_name']} untuk kelas {$cp['class_name']}",
                    'actual_start_date'  => $now->copy()->startOfWeek(),
                    'week_1_start_date'  => $now->copy()->startOfWeek(),
                    'end_date'           => $now->copy()->addMonths(3),
                    'project_leader_id'  => $projectLeader->id,
                    'status'             => 'ACTIVE',
                ]);

                $divisionsData = [];

                // ---- 3 Divisi ----
                foreach ($cp['divisions'] as $divInfo) {

                    // Ketua divisi
                    $divisionLeader = $this->createStudentUser(
                        $faker,
                        $divInfo['name'] . ' leader',
                        $pass,
                        $now
                    );
                    $this->insertClassMembership($class->id, $divisionLeader->id, $now);

                    $divisionId = DB::table('project_divisions')->insertGetId([
                        'project_id'     => $project->id,
                        'name'           => $divInfo['name'],
                        'code'           => $divInfo['code'],
                        'description'    => "Divisi {$divInfo['name']}",
                        'leader_user_id' => $divisionLeader->id,
                        'weight'         => 1.00,
                        'is_active'      => true,
                        'created_at'     => $now,
                        'updated_at'     => $now,
                    ]);

                    // Ketua divisi dimasukkan sebagai member
                    $this->insertProjectMember($project->id, $divisionId, $divisionLeader->id, $now);

                    // 6 anggota
                    $members = [];
                    for ($i = 1; $i <= 6; $i++) {
                        $member = $this->createStudentUser(
                            $faker,
                            $divInfo['name'] . ' member ' . $i,
                            $pass,
                            $now
                        );
                        $this->insertClassMembership($class->id, $member->id, $now);
                        $this->insertProjectMember($project->id, $divisionId, $member->id, $now);

                        $members[] = $member;
                    }

                    $divisionsData[] = [
                        'id'      => $divisionId,
                        'name'    => $divInfo['name'],
                        'leader'  => $divisionLeader,
                        'members' => $members,
                    ];
                }

                $createdProjects[] = [
                    'project'   => $project,
                    'leader'    => $projectLeader,
                    'divisions' => $divisionsData,
                ];
            }

            // ==========================================
            // 4. PROJECT WEEKS (4 minggu per project)
            // ==========================================
            foreach ($createdProjects as &$cp) {
                $cp['weeks'] = $this->createProjectWeeks($cp['project'], $now);
            }
            unset($cp);

            // ==========================================
            // 5. REPORTS
            //    - PERSONAL  : semua anggota + ketua divisi
            //    - DIVISION  : ketua divisi
            //    - PROJECT   : ketua project (sebagai PERSONAL divisi pertama)
            // ==========================================
            $this->seedReports($createdProjects, $now);
        });
    }

    // ==========================================================
    // HELPERS
    // ==========================================================

    private function createAdmins(string $pass, Carbon $now): void
    {
        $admins = [
            ['name' => 'Super Administrator', 'username' => 'superadmin',       'email' => 'superadmin@monitoring.com'],
            ['name' => 'Admin Akademik',      'username' => 'admin_akademik',   'email' => 'admin.akademik@monitoring.com'],
            ['name' => 'Admin Monitoring',    'username' => 'admin_monitoring', 'email' => 'admin.monitoring@monitoring.com'],
        ];


        foreach ($admins as $a) {
            User::create([
                'name'              => $a['name'],
                'username'          => $a['username'],
                'email'             => $a['email'],
                'password'          => $pass,
                'is_admin'          => true,
                'is_active'         => true,
                'email_verified_at' => $now,
                'remember_token'    => Str::random(10),
            ]);
        }
    }

    private function createStudentUser($faker, string $label, string $pass, Carbon $now): User
    {
        do {
            $name = $faker->unique()->name();
            $baseUsername = Str::slug($name, '_');
            if ($baseUsername === '') $baseUsername = 'user';

            $username = $baseUsername;
            $counter = 1;
            while (User::where('username', $username)->exists()) {
                $username = $baseUsername . $counter++;
            }

            $email = $username . '@monitoring.com';
        } while (User::where('email', $email)->exists());

        return User::create([
            'name'              => $name . ' (' . $label . ')',
            'username'          => $username,
            'email'             => $email,
            'password'          => $pass,
            'is_admin'          => false,
            'is_active'         => true,
            'email_verified_at' => $now,
            'remember_token'    => Str::random(10),
        ]);
    }

    private function insertClassMembership(int $classId, int $userId, Carbon $now): void
    {
        DB::table('class_memberships')->insert([
            'class_id'   => $classId,
            'user_id'    => $userId,
            'joined_at'  => $now->toDateString(),
            'left_at'    => null,
            'is_active'  => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function insertProjectMember(int $projectId, int $divisionId, int $userId, Carbon $now): void
    {
        DB::table('project_members')->insert([
            'project_id'  => $projectId,
            'division_id' => $divisionId,
            'user_id'     => $userId,
            'joined_at'   => $now->toDateString(),
            'left_at'     => null,
            'is_active'   => true,
            'created_at'  => $now,
            'updated_at'  => $now,
        ]);
    }

    private function createEvaluationLabels(Carbon $now): void
    {
        $labels = [
            ['name' => 'Sangat Baik', 'sort_order' => 1],
            ['name' => 'Baik',        'sort_order' => 2],
            ['name' => 'Cukup',       'sort_order' => 3],
            ['name' => 'Kurang',      'sort_order' => 4],
        ];

        foreach ($labels as $l) {
            DB::table('report_evaluation_labels')->insert([
                'name'        => $l['name'],
                'description' => "Label evaluasi {$l['name']}",
                'sort_order'  => $l['sort_order'],
                'is_active'   => true,
                'created_at'  => $now,
                'updated_at'  => $now,
            ]);
        }
    }

    private function createProjectWeeks(Project $project, Carbon $now): array
    {
        $weeks = [];
        $start = Carbon::parse($project->week_1_start_date);

        for ($i = 1; $i <= 4; $i++) {
            $weekStart = $start->copy()->addWeeks($i - 1);
            $weekEnd   = $weekStart->copy()->addDays(6);

            $weekId = DB::table('project_weeks')->insertGetId([
                'project_id'        => $project->id,
                'week_number'       => $i,
                'week_start'        => $weekStart->toDateString(),
                'week_end'          => $weekEnd->toDateString(),
                'report_open_at'    => $weekStart->copy()->setTime(0, 0),
                'report_close_at'   => $weekEnd->copy()->setTime(23, 59),
                'revision_close_at' => $weekEnd->copy()->addDays(2)->setTime(23, 59),
                'created_at'        => $now,
                'updated_at'        => $now,
            ]);

            $weeks[] = [
                'id'     => $weekId,
                'number' => $i,
                'start'  => $weekStart,
                'end'    => $weekEnd,
            ];
        }

        return $weeks;
    }

    private function seedReports(array $createdProjects, Carbon $now): void
    {
        foreach ($createdProjects as $cp) {
            $project       = $cp['project'];
            $projectLeader = $cp['leader'];
            $divisions     = $cp['divisions'];
            $weeks         = $cp['weeks'];

            foreach ($weeks as $week) {
                $weekId      = $week['id'];
                $submittedAt = $week['end']->copy()->setTime(20, 0);

                // ---- Laporan PERSONAL semua anggota + ketua divisi ----
                foreach ($divisions as $div) {
                    $personalUsers = array_merge([$div['leader']], $div['members']);

                    foreach ($personalUsers as $user) {
                        DB::table('reports')->insert([
                            'project_id'          => $project->id,
                            'project_week_id'     => $weekId,
                            'author_id'           => $user->id,
                            'division_id'         => $div['id'],
                            'type'                => 'PERSONAL',
                            'title'               => "Laporan Personal Minggu ke-{$week['number']} - {$user->name}",
                            'work_done'           => "Mengerjakan task divisi {$div['name']} minggu ke-{$week['number']}.",
                            'achievements'        => 'Task utama selesai sesuai deadline.',
                            'obstacles'           => 'Ada kendala teknis minor.',
                            'solutions'           => 'Diskusi dan pairing dengan anggota lain.',
                            'next_plan'           => 'Melanjutkan task yang belum tuntas.',
                            'support_needed'      => 'Review dari ketua divisi.',
                            'progress_percentage' => null,
                            'status'              => 'SUBMITTED',
                            'revision_count'      => 0,
                            'reviewed_by'         => null,
                            'reviewed_at'         => null,
                            'evaluation_label_id' => null,
                            'review_comment'      => null,
                            'decided_by'          => null,
                            'decided_at'          => null,
                            'created_at'          => $submittedAt,
                            'updated_at'          => $submittedAt,
                        ]);
                    }
                }

                // ---- Laporan DIVISION dari ketua divisi ----
                foreach ($divisions as $div) {
                    DB::table('reports')->insert([
                        'project_id'          => $project->id,
                        'project_week_id'     => $weekId,
                        'author_id'           => $div['leader']->id,
                        'division_id'         => $div['id'],
                        'type'                => 'DIVISION',
                        'title'               => "Laporan Divisi {$div['name']} Minggu ke-{$week['number']}",
                        'work_done'           => "Rekap progress divisi {$div['name']} minggu ke-{$week['number']}.",
                        'achievements'        => 'Target divisi tercapai sebagian besar.',
                        'obstacles'           => 'Koordinasi antar anggota.',
                        'solutions'           => 'Rapat divisi mingguan.',
                        'next_plan'           => 'Fokus pada task prioritas.',
                        'support_needed'      => 'Dukungan tools dan resource.',
                        'progress_percentage' => 25.00 * $week['number'],
                        'status'              => 'SUBMITTED',
                        'revision_count'      => 0,
                        'reviewed_by'         => null,
                        'reviewed_at'         => null,
                        'evaluation_label_id' => null,
                        'review_comment'      => null,
                        'decided_by'          => null,
                        'decided_at'          => null,
                        'created_at'          => $submittedAt,
                        'updated_at'          => $submittedAt,
                    ]);
                }

                // ---- Laporan PROJECT dari ketua project ----
                // Schema reports wajib punya division_id, jadi pakai divisi pertama.
                $firstDiv = $divisions[0];
                DB::table('reports')->insert([
                    'project_id'          => $project->id,
                    'project_week_id'     => $weekId,
                    'author_id'           => $projectLeader->id,
                    'division_id'         => $firstDiv['id'],
                    'type'                => 'PERSONAL',
                    'title'               => "Laporan Project Minggu ke-{$week['number']} - {$project->name}",
                    'work_done'           => "Rekap keseluruhan progress project {$project->name} minggu ke-{$week['number']}.",
                    'achievements'        => 'Project berjalan sesuai milestone.',
                    'obstacles'           => 'Beberapa divisi butuh tambahan waktu.',
                    'solutions'           => 'Re-scheduling task antar divisi.',
                    'next_plan'           => 'Fokus pada deliverable minggu depan.',
                    'support_needed'      => 'Dukungan pembimbing dan fasilitas.',
                    'progress_percentage' => 25.00 * $week['number'],
                    'status'              => 'SUBMITTED',
                    'revision_count'      => 0,
                    'reviewed_by'         => null,
                    'reviewed_at'         => null,
                    'evaluation_label_id' => null,
                    'review_comment'      => null,
                    'decided_by'          => null,
                    'decided_at'          => null,
                    'created_at'          => $submittedAt,
                    'updated_at'          => $submittedAt,
                ]);
            }
        }
    }
}
