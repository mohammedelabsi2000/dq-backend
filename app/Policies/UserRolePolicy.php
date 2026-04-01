<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserRolePolicy
{
    use HandlesAuthorization;

    // عرض أدوار المستخدم
    public function index($user, User $target)
    {
        // return $user->hasAbility('roles.view', $target);
        return $user->hasAbility('roles.view');
    }

    // إسناد دور
    public function assign($user, User $target)
    {
        // return $user->hasAbility('roles.update', $target);
        return $user->hasAbility('roles.create');
    }

    // تحديث دور
    public function sync($user, User $target)
    {
        // return $user->hasAbility('roles.update', $target);
        return $user->hasAbility('roles.update');
    }

    // إزالة دور
    public function remove($user, User $target)
    {
        // return $user->hasAbility('roles.update', $target);
        return $user->hasAbility('roles.delete');
    }
}
