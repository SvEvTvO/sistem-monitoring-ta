<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use App\Traits\Auditable;

class User extends Authenticatable
{
    use HasFactory, Notifiable, Auditable;

    protected $guarded = ['id'];
    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
        ];
    }

    public function classMemberships() { return $this->hasMany(ClassMembership::class); }
    public function projectMembers() { return $this->hasMany(ProjectMember::class); }
    public function ledProjects() { return $this->hasMany(Project::class, 'project_leader_id'); }
    public function ledDivisions() { return $this->hasMany(ProjectDivision::class, 'leader_user_id'); }
    public function reports() { return $this->hasMany(Report::class, 'author_id'); }
}
