<?php

namespace App\Policies;

use App\Models\Center;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CenterPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('centers.show');
    }

    public function view(User $user, Center $center): bool
    {
        return $user->hasPermissionTo('centers.show')
            && $this->isVisible($user, $center);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('centers.create');
    }

    public function update(User $user, Center $center): bool
    {
        return $user->hasPermissionTo('centers.update')
            && $this->isVisible($user, $center);
    }

    public function delete(User $user, Center $center): bool
    {
        return $user->hasPermissionTo('centers.delete')
            && $this->isVisible($user, $center);
    }

    private function isVisible(User $user, Center $center): bool
    {
        return Center::visibleTo($user)->where('id', $center->id)->exists();
    }
}
