<?php

namespace App\Policies;

use App\Models\Report;
use App\Models\User;
use App\Models\ProjectMember;

class ReportPolicy
{
    /**
     * Hak akses melihat laporan: 
     * - Pembuat laporan
     * - Ketua Divisi (untuk divisinya)
     * - Ketua Project (untuk seluruh project)
     * - Anggota (hanya bisa melihat laporan divisi yang sudah APPROVED)
     */
    public function view(User $user, Report $report): bool
    {
        if ($user->id === $report->author_id) return true;

        $project = $report->project;
        
        // Ketua project bisa melihat semua
        if ($project->project_leader_id === $user->id) return true;
        
        // Ketua divisi bisa melihat laporan di divisinya
        if ($report->division->leader_user_id === $user->id) return true;

        // Anggota bisa melihat laporan divisi yang sudah APPROVED[cite: 1, 3]
        if ($report->type === 'DIVISION' && $report->status === 'APPROVED') {
            $isMember = ProjectMember::where('project_id', $project->id)
                ->where('user_id', $user->id)
                ->exists();
            if ($isMember) return true;
        }

        return false;
    }

    /**
     * Hak edit laporan:
     * Laporan hanya bisa diedit selama statusnya SUBMITTED, REVIEWED, atau REVISION_REQUIRED[cite: 1, 3].
     * Status APPROVED mengunci laporan[cite: 3].
     */
    public function update(User $user, Report $report): bool
    {
        if ($user->id !== $report->author_id) return false;

        return in_array($report->status, ['SUBMITTED', 'REVIEWED', 'REVISION_REQUIRED']);
    }

    /**
     * Hak melakukan review:
     * 1. Tidak boleh mereview laporan sendiri[cite: 1, 3].
     * 2. Laporan divisi direview oleh Ketua Project[cite: 1, 3].
     * 3. Laporan personal direview oleh Ketua Divisi[cite: 1, 3].
     */
    public function review(User $user, Report $report): bool
    {
        // Cegah Self-Review[cite: 1, 3]
        if ($user->id === $report->author_id) return false;

        // Reviewer laporan divisi -> Ketua Project[cite: 1]
        if ($report->type === 'DIVISION') {
            return $report->project->project_leader_id === $user->id;
        }

        // Reviewer laporan personal -> Ketua Divisi (termasuk laporan pribadi Ketua Project)[cite: 1]
        if ($report->type === 'PERSONAL') {
            return $report->division->leader_user_id === $user->id;
        }

        return false;
    }
}