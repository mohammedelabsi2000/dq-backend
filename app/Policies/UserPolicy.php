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
        return $user->hasPermissionTo('users.show')
            && $this->isVisible($user, $target);
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('users.create');
    }

    public function update(User $user, User $target): bool
    {
        return $user->hasPermissionTo('users.update')
            && $this->isVisible($user, $target);
    }

    public function delete(User $user, User $target): bool
    {
        return $user->hasPermissionTo('users.delete')
            && $this->isVisible($user, $target);
    }

    public function restore(User $user, User $target): bool
    {
        return $user->hasPermissionTo('users.restore')
            && $this->isVisible($user, $target);
    }

    public function toggleActive(User $user, User $target): bool
    {
        return $user->hasPermissionTo('users.toggle_active')
            && $this->isVisible($user, $target);
    }

    private function isVisible(User $user, User $target): bool
    {
        return User::visibleTo($user)->where('id', $target->id)->exists();
    }
}
