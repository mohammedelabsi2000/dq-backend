<?php

namespace App\Policies;

use App\Models\Quran\CustomJuz;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CustomJuzPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function viewAny($user)
    {
        return $user->hasPermissionTo('custom_juzs.show');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Quran\CustomJuz  $customJuz
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, CustomJuz $customJuz)
    {
        return $user->hasPermissionTo('custom_juzs.show');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user)
    {
        return $user->hasPermissionTo('custom_juzs.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Quran\CustomJuz  $customJuz
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, CustomJuz $customJuz)
    {
        return $user->hasPermissionTo('custom_juzs.update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Quran\CustomJuz  $customJuz
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, CustomJuz $customJuz)
    {
        return $user->hasPermissionTo('custom_juzs.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Quran\CustomJuz  $customJuz
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore($user, CustomJuz $customJuz)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Quran\CustomJuz  $customJuz
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete($user, CustomJuz $customJuz)
    {
        //
    }
}
