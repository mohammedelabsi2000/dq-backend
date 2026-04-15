<?php

namespace App\Policies;

use App\Models\AcademicQualification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AcademicQualificationPolicy
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
     * @param  \App\Models\AcademicQualification  $academicQualification
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, AcademicQualification $qualification)
    {
        return $user->hasPermissionTo('users.certificates.show') || $user->hasPermissionTo('students.certificates.show');
    }

    /**
     * Determine whether user can create models.
     *
     * @param  \App\Models\User  $user
     * @param  mixed  $person
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user, $person = null)
    {
        return $user->hasPermissionTo('users.certificates.update') || $user->hasPermissionTo('students.certificates.update');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AcademicQualification  $academicQualification
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, AcademicQualification $qualification)
    {
        return $user->hasPermissionTo('users.certificates.update') || $user->hasPermissionTo('students.certificates.update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AcademicQualification  $academicQualification
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, AcademicQualification $qualification)
    {
        return $user->hasPermissionTo('users.certificates.update') || $user->hasPermissionTo('students.certificates.update');
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AcademicQualification  $academicQualification
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore($user, AcademicQualification $academicQualification)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\AcademicQualification  $academicQualification
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete($user, AcademicQualification $academicQualification)
    {
        //
    }
}
