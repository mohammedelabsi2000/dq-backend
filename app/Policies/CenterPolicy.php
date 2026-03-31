<?php

namespace App\Policies;

use App\Models\Center;
use App\Models\Region;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CenterPolicy
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
        return $user->hasAbility('centers.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Center  $center
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, Center $center)
    {
        return $user->hasAbility('centers.view', $center);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user, Region $region)
    {
        // dd($user->hasAbility('centers.create', $region));
        return $user->hasAbility('centers.create', $region);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Center  $center
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, Center $center)
    {
        return $user->hasAbility('centers.update', $center);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Center  $center
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, Center $center)
    {
        return $user->hasAbility('centers.delete', $center);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Center  $center
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore($user, Center $center)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Center  $center
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete($user, Center $center)
    {
        //
    }
}
