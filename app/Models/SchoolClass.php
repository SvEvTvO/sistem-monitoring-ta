<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SchoolClass extends Model
{
    protected $table = 'classes';
    protected $guarded = ['id'];

    public function department() { return $this->belongsTo(Department::class); }
    public function level() { return $this->belongsTo(Level::class); }
    public function academicYear() { return $this->belongsTo(AcademicYear::class); }
    public function projects() { return $this->hasMany(Project::class, 'class_id'); }

    /**
     * Relasi ke tabel class_memberships (Data anggota/siswa di kelas ini)
     */
    public function classMemberships()
    {
        return $this->hasMany(ClassMembership::class, 'class_id');
    }


}
