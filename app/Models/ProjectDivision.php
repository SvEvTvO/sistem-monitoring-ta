<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectDivision extends Model
{
    protected $guarded = ['id'];

    public function project() { return $this->belongsTo(Project::class); }
    public function leader() { return $this->belongsTo(User::class, 'leader_user_id'); }
    public function members() { return $this->hasMany(ProjectMember::class, 'division_id'); }
    public function reports() { return $this->hasMany(Report::class, 'division_id'); }
}