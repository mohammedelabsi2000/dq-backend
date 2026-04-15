<?php

namespace App\Policies;

use App\Models\PersonalCourse;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PersonalCoursePolicy
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
        return $user->hasPermissionTo('users.certificates.show') || $user->hasPermissionTo('students.certificates.show');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user)
    {
        return $user->hasPermissionTo('users.certificates.show') || $user->hasPermissionTo('students.certificates.show');
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user)
    {
        return $user->hasPermissionTo('users.certificates.update') || $user->hasPermissionTo('students.certificates.update');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user)
    {
        return $user->hasPermissionTo('users.certificates.update') || $user->hasPermissionTo('students.certificates.update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user)
    {
        return $user->hasPermissionTo('users.certificates.update') || $user->hasPermissionTo('students.certificates.update');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore($user)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PersonalCourse  $personalCourse
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete($user, PersonalCourse $personalCourse)
    {
        //
    }
}
