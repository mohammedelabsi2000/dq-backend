<?php

namespace App\Concerns;

use App\Models\Role;

trait HasRoles
{
    public function roles()
    {
        return $this->morphToMany(Role::class, 'authorizable', 'role_user');
    }

    public function hasAbility($ability)
    {
        return $this->roles()->whereHas('roleAbilities', function ($query) use ($ability) {
            $query->where('ability', $ability)
                ->where('type', '=', 'allow');
        })->exists();
    }
}
