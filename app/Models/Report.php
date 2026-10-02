<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Report extends Model
{
    protected $guarded = ['id'];

    public function project() { return $this->belongsTo(Project::class); }
    public function projectWeek() { return $this->belongsTo(ProjectWeek::class); }
    public function author() { return $this->belongsTo(User::class, 'author_id'); }
    public function division() { return $this->belongsTo(ProjectDivision::class); }
    public function reviewer() { return $this->belongsTo(User::class, 'reviewed_by'); }
    public function decider() { return $this->belongsTo(User::class, 'decided_by'); }
    public function evaluationLabel() { return $this->belongsTo(ReportEvaluationLabel::class); }
    public function histories() { return $this->hasMany(ReportHistory::class); }
}