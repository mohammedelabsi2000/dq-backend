<?php

namespace App\Policies;

use App\Models\Halaqa;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HalaqaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('halaqas.show');
    }

    public function view(User $user, Halaqa $halaqa): bool
    {
        return $user->hasPermissionTo('halaqas.show');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('halaqas.create');
    }

    public function update(User $user, Halaqa $halaqa): bool
    {
        return $user->hasPermissionTo('halaqas.update');
    }

    public function delete(User $user, Halaqa $halaqa): bool
    {
        return $user->hasPermissionTo('halaqas.delete');
    }
}
