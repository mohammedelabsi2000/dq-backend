<?php

namespace App\Policies;

use App\Models\Mosque;
use App\Models\Region;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class MosquePolicy
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
        return $user->hasPermissionTo('mosques.show');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, Mosque $mosque)
    {
        return $user->hasPermissionTo('mosques.show')
            && $this->isVisible($user, $mosque);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user)
    {
        return $user->hasPermissionTo('mosques.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, Mosque $mosque)
    {
        return $user->hasPermissionTo('mosques.update')
            && $this->isVisible($user, $mosque);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, Mosque $mosque)
    {
        return $user->hasPermissionTo('mosques.delete')
            && $this->isVisible($user, $mosque);
    }

    private function isVisible($user, Mosque $mosque): bool
    {
        return Mosque::visibleTo($user)->where('id', $mosque->id)->exists();
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore($user, Mosque $mosque)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Mosque  $mosque
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete($user, Mosque $mosque)
    {
        //
    }
}
