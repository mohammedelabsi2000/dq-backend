<?php

namespace App\Policies;

use App\Models\Constant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConstantPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('constants.show');
    }

    public function view(User $user, Constant $constant): bool
    {
        return $user->hasPermissionTo('constants.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('constants.create');
    }

    public function update(User $user, Constant $constant): bool
    {
        return $user->hasPermissionTo('constants.update');
    }

    public function delete(User $user, Constant $constant): bool
    {
        return $user->hasPermissionTo('constants.delete');
    }
}
