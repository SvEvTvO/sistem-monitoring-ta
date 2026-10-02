<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectMember extends Model
{
    protected $guarded = ['id'];

    public function project() { return $this->belongsTo(Project::class); }
    public function division() { return $this->belongsTo(ProjectDivision::class); }
    public function user() { return $this->belongsTo(User::class); }
}