<?php

namespace App\Services;

use App\Models\Report;
use App\Models\User;
use App\Models\ReportHistory;
use Exception;
use Illuminate\Support\Facades\DB;

class ReportReviewService
{
    /**
     * Menandai laporan sudah dibaca (SUBMITTED -> REVIEWED)
     */
    public function markAsReviewed(Report $report, User $reviewer)
    {
        if ($report->status !== 'SUBMITTED') {
            return $report;
        }

        DB::transaction(function () use ($report, $reviewer) {
            $oldStatus = $report->status;
            $report->update([
                'status' => 'REVIEWED',
                'reviewed_by' => $reviewer->id,
                'reviewed_at' => now(),
            ]);

            ReportHistory::create([
                'report_id' => $report->id,
                'actor_id' => $reviewer->id,
                'old_status' => $oldStatus,
                'new_status' => 'REVIEWED',
                'revision_number' => $report->revision_count,
            ]);
        });

        return $report;
    }

    /**
     * Menyetujui laporan dengan memberikan label dan komentar (REVIEWED -> APPROVED)
     */
    public function approve(Report $report, User $reviewer, array $data)
    {
        if ($report->status !== 'REVIEWED') {
            throw new Exception("Laporan harus dibaca (REVIEWED) terlebih dahulu.");
        }

        DB::transaction(function () use ($report, $reviewer, $data) {
            $oldStatus = $report->status;
            
            $report->update([
                'status' => 'APPROVED',
                'evaluation_label_id' => $data['evaluation_label_id'],
                'review_comment' => $data['review_comment'],
                'decided_by' => $reviewer->id,
                'decided_at' => now(),
            ]);

            ReportHistory::create([
                'report_id' => $report->id,
                'actor_id' => $reviewer->id,
                'old_status' => $oldStatus,
                'new_status' => 'APPROVED',
                'revision_number' => $report->revision_count,
                'evaluation_label_id' => $data['evaluation_label_id'],
                'comment' => $data['review_comment'],
            ]);
        });

        return $report;
    }

    /**
     * Meminta revisi laporan (REVIEWED -> REVISION_REQUIRED)
     */
    public function requestRevision(Report $report, User $reviewer, array $data)
    {
        if ($report->status !== 'REVIEWED') {
            throw new Exception("Laporan harus dibaca (REVIEWED) terlebih dahulu.");
        }

        DB::transaction(function () use ($report, $reviewer, $data) {
            $oldStatus = $report->status;
            
            $report->update([
                'status' => 'REVISION_REQUIRED',
                'evaluation_label_id' => $data['evaluation_label_id'],
                'review_comment' => $data['review_comment'],
                'decided_by' => $reviewer->id,
                'decided_at' => now(),
            ]);

            ReportHistory::create([
                'report_id' => $report->id,
                'actor_id' => $reviewer->id,
                'old_status' => $oldStatus,
                'new_status' => 'REVISION_REQUIRED',
                'revision_number' => $report->revision_count,
                'evaluation_label_id' => $data['evaluation_label_id'],
                'comment' => $data['review_comment'],
            ]);
        });

        return $report;
    }
}