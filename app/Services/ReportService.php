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
        // 1. Cek apakah waktu pelaporan sedang buka
        if (!$this->projectWeekService->isReportWindowOpen($week)) {
            throw new Exception("Waktu pelaporan mingguan sudah ditutup atau belum dibuka.");
        }

        // 2. Cek aturan: 1 Laporan Pribadi per Minggu per User
        $existingReport = Report::where('project_week_id', $week->id)
            ->where('author_id', $user->id)
            ->where('type', 'PERSONAL')
            ->first();

        if ($existingReport) {
            throw new Exception("Kamu sudah membuat laporan personal untuk minggu ini.");
        }

        // 3. Cari divisi user di project ini
        $member = $user->projectMembers()->where('project_id', $week->project_id)->first();
        if (!$member) {
            throw new Exception("Kamu bukan anggota project ini.");
        }

        // 4. Buat laporan
        return Report::create([
            'project_id' => $week->project_id,
            'project_week_id' => $week->id,
            'author_id' => $user->id,
            'division_id' => $member->division_id,
            'type' => 'PERSONAL',
            'title' => $data['title'],
            'work_done' => $data['work_done'],
            'achievements' => $data['achievements'] ?? null,
            'obstacles' => $data['obstacles'] ?? null,
            'solutions' => $data['solutions'] ?? null,
            'next_plan' => $data['next_plan'],
            'support_needed' => $data['support_needed'] ?? null,
            'status' => 'SUBMITTED', // Aturan: Langsung tercatat sebagai laporan resmi
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

        // Cari divisi yang dipimpin user di project ini
        $ledDivision = $user->ledDivisions()->where('project_id', $week->project_id)->first();
        if (!$ledDivision) {
            throw new Exception("Kamu bukan ketua divisi di project ini.");
        }

        // Cek aturan: 1 Laporan Divisi per Minggu[cite: 1, 3]
        $existingReport = Report::where('project_week_id', $week->id)
            ->where('division_id', $ledDivision->id)
            ->where('type', 'DIVISION')
            ->first();

        if ($existingReport) {
            throw new Exception("Divisimu sudah membuat laporan divisi untuk minggu ini.");
        }

        return Report::create([
            'project_id' => $week->project_id,
            'project_week_id' => $week->id,
            'author_id' => $user->id,
            'division_id' => $ledDivision->id,
            'type' => 'DIVISION',
            'title' => $data['title'],
            'work_done' => $data['work_done'],
            'achievements' => $data['achievements'] ?? null,
            'obstacles' => $data['obstacles'] ?? null,
            'solutions' => $data['solutions'] ?? null,
            'next_plan' => $data['next_plan'],
            'support_needed' => $data['support_needed'] ?? null,
            'progress_percentage' => $data['progress_percentage'], // Wajib ada untuk Laporan Divisi[cite: 1, 3]
            'status' => 'SUBMITTED',
        ]);
    }
}