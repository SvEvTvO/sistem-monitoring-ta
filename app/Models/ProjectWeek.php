<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectWeek extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'week_start' => 'date',
            'week_end' => 'date',
            'report_open_at' => 'datetime',
            'report_close_at' => 'datetime',
            'revision_close_at' => 'datetime',
        ];
    }

    public function project() { return $this->belongsTo(Project::class); }
    public function reports() { return $this->hasMany(Report::class); }
}