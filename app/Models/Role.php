<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';

    public function admins()
    {
        return $this->hasMany(Admin::class);
    }

    // Abilities assigned to this role
    public function abilities()
    {
        return $this->belongsToMany(Ability::class, 'role_abilities', 'role_id', 'ability_id');
    }
}
