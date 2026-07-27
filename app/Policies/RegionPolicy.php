<?php

namespace App\Policies;

use App\Models\Region;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RegionPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('regions.show');
    }

    public function view(User $user, Region $region): bool
    {
        return $user->hasPermissionTo('regions.show')
            && $this->isVisible($user, $region);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('regions.create');
    }

    public function update(User $user, Region $region): bool
    {
        return $user->hasPermissionTo('regions.update')
            && $this->isVisible($user, $region);
    }

    public function delete(User $user, Region $region): bool
    {
        return $user->hasPermissionTo('regions.delete')
            && $this->isVisible($user, $region);
    }

    private function isVisible(User $user, Region $region): bool
    {
        return Region::visibleTo($user)->where('id', $region->id)->exists();
    }
}
