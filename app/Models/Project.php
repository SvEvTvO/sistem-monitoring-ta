<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $guarded = ['id'];

    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function leader() { return $this->belongsTo(User::class, 'project_leader_id'); }
    public function divisions() { return $this->hasMany(ProjectDivision::class); }
    public function members() { return $this->hasMany(ProjectMember::class); }
    public function weeks() { return $this->hasMany(ProjectWeek::class); }
    public function reports() { return $this->hasMany(Report::class); }
    public function targets() { return $this->hasMany(ProjectTarget::class); }
}