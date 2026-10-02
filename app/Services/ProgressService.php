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
        $divisions = $project->divisions()->where('is_active', true)->get();
        
        if ($divisions->isEmpty()) {
            return 0;
        }

        $totalProgress = 0;
        $totalWeight = 0;

        foreach ($divisions as $division) {
            // Ambil Laporan Divisi terakhir yang statusnya APPROVED
            $latestApprovedReport = Report::where('project_id', $project->id)
                ->where('division_id', $division->id)
                ->where('type', 'DIVISION')
                ->where('status', 'APPROVED')
                ->latest('project_week_id') // Urutkan dari minggu paling baru
                ->first();

            $divisionProgress = $latestApprovedReport ? $latestApprovedReport->progress_percentage : 0;
            
            // Perhitungan dengan bobot (default bobot adalah 1.00)
            $totalProgress += ($divisionProgress * $division->weight);
            $totalWeight += $division->weight;
        }

        // Hindari division by zero
        return $totalWeight > 0 ? round($totalProgress / $totalWeight, 2) : 0;
    }
}