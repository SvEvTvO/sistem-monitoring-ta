<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ClassMembership extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'joined_at' => 'date',
            'left_at' => 'date',
        ];
    }

    public function schoolClass() { return $this->belongsTo(SchoolClass::class, 'class_id'); }
    public function user() { return $this->belongsTo(User::class); }
}