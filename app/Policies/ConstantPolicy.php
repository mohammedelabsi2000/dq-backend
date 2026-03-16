<?php

namespace App\Policies;

use App\Models\Constant;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ConstantPolicy
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
        return $user->hasAbility('constants.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Constant  $constant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, Constant $constant)
    {
        return $user->hasAbility('constants.view');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user)
    {
        return $user->hasAbility('constants.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Constant  $constant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, Constant $constant)
    {
        return $user->hasAbility('constants.update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Constant  $constant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, Constant $constant)
    {
        return $user->hasAbility('constants.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Constant  $constant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore($user, Constant $constant)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Constant  $constant
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete($user, Constant $constant)
    {
        //
    }
}
