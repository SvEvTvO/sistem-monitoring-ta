<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectTarget extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'deadline' => 'date',
            'completed_at' => 'datetime',
        ];
    }

    public function project() { return $this->belongsTo(Project::class); }
    public function creator() { return $this->belongsTo(User::class, 'created_by'); }
    public function completer() { return $this->belongsTo(User::class, 'completed_by'); }
}