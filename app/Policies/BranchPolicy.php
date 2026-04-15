<?php

namespace App\Policies;

use App\Models\Branch;
use Illuminate\Auth\Access\HandlesAuthorization;

class BranchPolicy
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
        // return $user->hasAbility('branches.show');
        // dd($user);
        // dd($user->hasPermissionTo('branches.show', 'sanctum'));
        return $user->hasPermissionTo('branches.show');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, Branch $branch)
    {
        // return $user->hasAbility('branches.show', $branch);
        return $user->hasPermissionTo('branches.show');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user)
    {
        // return $user->hasAbility('branches.create');
        return $user->hasPermissionTo('branches.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, Branch $branch)
    {
        // return $user->hasAbility('branches.update', $branch);
        return $user->hasPermissionTo('branches.update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, Branch $branch)
    {
        // return $user->hasAbility('branches.delete', $branch);
        return $user->hasPermissionTo('branches.delete');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore($user, Branch $branch)
    {
        // return $user->hasAbility('branches.restore', $branch);
        return $user->hasPermissionTo('branches.restore');
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\Branch  $branch
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete($user, Branch $branch)
    {
        // return $user->hasAbility('branches.forceDelete', $branch);
        return $user->hasPermissionTo('branches.forceDelete');
    }
}
