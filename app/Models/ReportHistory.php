<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportHistory extends Model
{
    protected $guarded = ['id'];

    public function report() { return $this->belongsTo(Report::class); }
    public function actor() { return $this->belongsTo(User::class, 'actor_id'); }
    public function evaluationLabel() { return $this->belongsTo(ReportEvaluationLabel::class); }
}