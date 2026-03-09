<?php

namespace App\Policies;

use App\Models\Region;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RegionPolicy
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
        return $user->hasAbility('regions.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, Region $region)
    {
        return $user->hasAbility('regions.view', $region);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user)
    {
        return $user->hasAbility('regions.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, Region $region)
    {
        return $user->hasAbility('regions.update', $region);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, Region $region)
    {
        return $user->hasAbility('regions.delete', $region);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore($user, Region $region)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Region  $region
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete($user, Region $region)
    {
        //
    }
}
