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
        // return $user->hasPermissionTo('roles.show', $target);
        return $user->hasPermissionTo('users.roles.update');
    }

    // إسناد دور
    public function assign($user, User $target)
    {
        // return $user->hasPermissionTo('roles.update', $target);
        return $user->hasPermissionTo('users.roles.update');
    }

    // تحديث دور
    public function sync($user, User $target)
    {
        // return $user->hasPermissionTo('roles.update', $target);
        return $user->hasPermissionTo('users.roles.update');
    }

    // إزالة دور
    public function remove($user, User $target)
    {
        // return $user->hasPermissionTo('roles.update', $target);
        return $user->hasPermissionTo('users.roles.update');
    }
}
