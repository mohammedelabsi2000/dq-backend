<?php

namespace App\Policies;

use App\Models\Center;
use App\Models\Halaqa;
use App\Models\Region;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HalaqaPolicy
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
        return $user->hasAbility('halaqas.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Halaqa  $halaqa
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, Halaqa $halaqa)
    {
        return $user->hasAbility('halaqas.view', $halaqa);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user, Region|Center $reference)
    {
        return $user->hasAbility('halaqas.create', $reference);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Halaqa  $halaqa
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, Halaqa $halaqa)
    {
        return $user->hasAbility('halaqas.update', $halaqa);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Halaqa  $halaqa
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, Halaqa $halaqa)
    {
        return $user->hasAbility('halaqas.delete', $halaqa);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Halaqa  $halaqa
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore($user, Halaqa $halaqa)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Halaqa  $halaqa
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete($user, Halaqa $halaqa)
    {
        //
    }
}
