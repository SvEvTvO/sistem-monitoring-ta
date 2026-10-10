<?php

namespace App\Services;

use App\Models\Report;
use App\Models\ProjectWeek;
use App\Models\User;
use Exception;

class ReportService
{
    protected $projectWeekService;

    public function __construct(ProjectWeekService $projectWeekService)
    {
        $this->projectWeekService = $projectWeekService;
    }

    /**
     * Membuat laporan personal untuk anggota/ketua divisi/ketua project
     */
    public function createPersonalReport(User $user, ProjectWeek $week, array $data)
    {
        if (!$this->projectWeekService->isReportWindowOpen($week)) {
            throw new Exception("Waktu pelaporan mingguan sudah ditutup atau belum dibuka.");
        }

        // [OPTIMASI TAHAP 1]: Gunakan exists() alih-alih first() untuk menghemat RAM
        $hasReport = Report::where('project_week_id', $week->id)
            ->where('author_id', $user->id)
            ->where('type', 'PERSONAL')
            ->exists();

        if ($hasReport) {
            throw new Exception("Kamu sudah membuat laporan personal untuk minggu ini.");
        }

        // [OPTIMASI TAHAP 2]: Gunakan value('division_id') karena kita HANYA butuh ID divisinya,
        // tidak perlu menarik seluruh model ProjectMember ke dalam memori.
        $divisionId = $user->projectMembers()
            ->where('project_id', $week->project_id)
            ->value('division_id');

        if (is_null($divisionId)) {
            throw new Exception("Kamu bukan anggota project ini.");
        }

        return Report::create([
            'project_id' => $week->project_id,
            'project_week_id' => $week->id,
            'author_id' => $user->id,
            'division_id' => $divisionId,
            'type' => 'PERSONAL',
            'title' => $data['title'],
            'work_done' => $data['work_done'],
            'achievements' => $data['achievements'] ?? null,
            'obstacles' => $data['obstacles'] ?? null,
            'solutions' => $data['solutions'] ?? null,
            'next_plan' => $data['next_plan'],
            'support_needed' => $data['support_needed'] ?? null,
            'status' => 'SUBMITTED', 
        ]);
    }

    /**
     * Membuat laporan divisi untuk ketua divisi
     */
    public function createDivisionReport(User $user, ProjectWeek $week, array $data)
    {
        if (!$this->projectWeekService->isReportWindowOpen($week)) {
            throw new Exception("Waktu pelaporan mingguan sudah ditutup atau belum dibuka.");
        }

        // [OPTIMASI TAHAP 3]: Gunakan value('id') untuk sekadar memastikan kepemimpinan dan mengambil ID-nya
        $divisionId = $user->ledDivisions()
            ->where('project_id', $week->project_id)
            ->value('id');

        if (is_null($divisionId)) {
            throw new Exception("Kamu bukan ketua divisi di project ini.");
        }

        $hasReport = Report::where('project_week_id', $week->id)
            ->where('division_id', $divisionId)
            ->where('type', 'DIVISION')
            ->exists();

        if ($hasReport) {
            throw new Exception("Divisimu sudah membuat laporan divisi untuk minggu ini.");
        }

        return Report::create([
            'project_id' => $week->project_id,
            'project_week_id' => $week->id,
            'author_id' => $user->id,
            'division_id' => $divisionId,
            'type' => 'DIVISION',
            'title' => $data['title'],
            'work_done' => $data['work_done'],
            'achievements' => $data['achievements'] ?? null,
            'obstacles' => $data['obstacles'] ?? null,
            'solutions' => $data['solutions'] ?? null,
            'next_plan' => $data['next_plan'],
            'support_needed' => $data['support_needed'] ?? null,
            'progress_percentage' => $data['progress_percentage'], 
            'status' => 'SUBMITTED',
        ]);
    }
}
