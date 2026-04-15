<?php

namespace App\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Spatie\Permission\Models\Role;
use App\Models\User;

class RolePolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('roles.show', 'sanctum');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('roles.show', 'sanctum');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('roles.create', 'sanctum');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('roles.update', 'sanctum');
    }

    public function delete(User $user, Role $role): bool
    {
        return $user->hasPermissionTo('roles.delete', 'sanctum');
    }
}
