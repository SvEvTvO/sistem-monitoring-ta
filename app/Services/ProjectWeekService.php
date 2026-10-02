<?php

namespace App\Services;

use App\Models\Project;
use App\Models\ProjectWeek;
use Carbon\Carbon;

class ProjectWeekService
{
    /**
     * Mendapatkan minggu aktif berdasarkan tanggal saat ini
     */
    public function getCurrentWeek(Project $project)
    {
        $now = Carbon::now();
        return $project->weeks()
            ->where('week_start', '<=', $now->toDateString())
            ->where('week_end', '>=', $now->toDateString())
            ->first();
    }

    /**
     * Mengecek apakah saat ini berada dalam periode pelaporan normal (Sabtu 00:01 - Minggu 23:59)
     */
    public function isReportWindowOpen(ProjectWeek $week)
    {
        $now = Carbon::now();
        return $now->between($week->report_open_at, $week->report_close_at);
    }

    /**
     * Mengecek apakah saat ini berada dalam periode revisi (batas akhir Kamis 23:59)
     */
    public function isRevisionWindowOpen(ProjectWeek $week)
    {
        $now = Carbon::now();
        return $now->lessThanOrEqualTo($week->revision_close_at);
    }
}