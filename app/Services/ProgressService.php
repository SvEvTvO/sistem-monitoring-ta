<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Report;

class ProgressService
{
    /**
     * Menghitung progress rata-rata project berdasarkan laporan divisi resmi (APPROVED)
     */
    public function calculateProjectProgress(Project $project)
    {
        // Tarik divisi yang aktif
        $divisions = $project->divisions()->where('is_active', true)->get();

        if ($divisions->isEmpty()) {
            return 0;
        }

        // [OPTIMASI TAHAP 1]: Tarik SEMUA laporan divisi yang APPROVED untuk project ini sekaligus.
        // Mencegah query N+1 di dalam looping foreach!
        $allLatestReports = Report::where('project_id', $project->id)
            ->where('type', 'DIVISION')
            ->where('status', 'APPROVED')
            ->orderBy('project_week_id', 'desc') // Urutkan dari minggu paling baru
            ->get()
            ->groupBy('division_id'); // Kelompokkan berdasarkan divisi

        $totalProgress = 0;
        $totalWeight = 0;

        foreach ($divisions as $division) {
            // [OPTIMASI TAHAP 2]: Ambil laporan terakhir dari collection di memori (bukan dari Database)
            // Karena sudah di-orderBy desc di atas, first() otomatis mengambil data paling baru
            $latestApprovedReport = $allLatestReports->has($division->id)
                ? $allLatestReports->get($division->id)->first()
                : null;

            $divisionProgress = $latestApprovedReport ? (float) $latestApprovedReport->progress_percentage : 0;

            // Perhitungan dengan bobot (default bobot adalah 1.00)
            // Fallback ke 1 jika property weight bernilai null/tidak ada
            $weight = $division->weight ?? 1;

            $totalProgress += ($divisionProgress * $weight);
            $totalWeight += $weight;
        }

        // Hindari division by zero
        return $totalWeight > 0 ? round($totalProgress / $totalWeight, 2) : 0;
    }
}
