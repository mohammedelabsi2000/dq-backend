<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('users.show');
    }

    public function view(User $user, User $target): bool
    {
        return $user->hasPermissionTo('users.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('users.create');
    }

    public function update(User $user, User $target): bool
    {
        return $user->hasPermissionTo('users.update');
    }

    public function delete(User $user, User $target): bool
    {
        return $user->hasPermissionTo('users.delete');
    }
}
