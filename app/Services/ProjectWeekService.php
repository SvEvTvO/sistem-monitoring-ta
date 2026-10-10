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
        // [OPTIMASI]: Simpan ke variabel agar fungsi toDateString() tidak dieksekusi 2 kali
        $today = Carbon::now()->toDateString();

        return $project->weeks()
            ->where('week_start', '<=', $today)
            ->where('week_end', '>=', $today)
            ->first();
    }

    /**
     * Mengecek apakah saat ini berada dalam periode pelaporan normal (Sabtu 00:01 - Minggu 23:59)
     */
    public function isReportWindowOpen(ProjectWeek $week)
    {
        // [FAIL-SAFE]: Pastikan tanggalnya ada
        if (!$week->report_open_at || !$week->report_close_at) {
            return false;
        }

        $now = Carbon::now();

        // [BUG FIX]: Bungkus dengan Carbon::parse() agar kebal dari error casting tipe data String vs DateTime
        $openAt = Carbon::parse($week->report_open_at);
        $closeAt = Carbon::parse($week->report_close_at);

        return $now->between($openAt, $closeAt);
    }

    /**
     * Mengecek apakah saat ini berada dalam periode revisi (batas akhir Kamis 23:59)
     */
    public function isRevisionWindowOpen(ProjectWeek $week)
    {
        if (!$week->revision_close_at) {
            return false;
        }

        $now = Carbon::now();
        $revisionCloseAt = Carbon::parse($week->revision_close_at);

        return $now->lessThanOrEqualTo($revisionCloseAt);
    }
}
