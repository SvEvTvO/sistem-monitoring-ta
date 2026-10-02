<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AcademicYear extends Model
{
    protected $guarded = ['id'];

    public function classes() { return $this->hasMany(SchoolClass::class, 'academic_year_id'); }
}