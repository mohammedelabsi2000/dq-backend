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
        return $user->hasAbility('roles.view', $target);
    }

    // إسناد دور
    public function assign($user, User $target)
    {
        return $user->hasAbility('roles.update', $target);
    }

    // تحديث دور
    public function sync($user, User $target)
    {
        return $user->hasAbility('roles.update', $target);
    }

    // إزالة دور
    public function remove($user, User $target)
    {
        return $user->hasAbility('roles.update', $target);
    }
}
