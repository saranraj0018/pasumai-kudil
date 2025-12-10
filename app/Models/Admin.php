<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Log;

class Admin extends Authenticatable
{
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    // Abilities through role
    public function abilities()
    {
        if ($this->relationLoaded('role')) {
            return $this->role ? $this->role->abilities : collect();
        }
        return $this->role ? $this->role->abilities : collect();
    }

    // Check if user has ability
    public function hasAbility($ability)
    {
        $abilities = $this->abilities()->pluck('ability')->toArray();
        return in_array($ability, $abilities);
    }
}
