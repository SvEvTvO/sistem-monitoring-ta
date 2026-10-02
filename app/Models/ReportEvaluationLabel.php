<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ReportEvaluationLabel extends Model
{
    protected $guarded = ['id'];

    public function reports() { return $this->hasMany(Report::class, 'evaluation_label_id'); }
    public function histories() { return $this->hasMany(ReportHistory::class, 'evaluation_label_id'); }
}