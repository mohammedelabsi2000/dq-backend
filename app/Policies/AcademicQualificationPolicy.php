<?php

namespace App\Policies;

use App\Models\AcademicQualification;
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
        return $user->hasAbility('users.view') || $user->hasAbility('students.view');
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
        $qualification->loadMissing('person');

        // صاحب الشهادة يشوف شهادته دائماً
        if (
            $qualification->person_type === 'user' &&
            $qualification->person_id === $user->id
        ) {
            return true;
        }

        if ($qualification->person_type === 'user') {
            return $user->hasAbility('users.view');
        }

        if ($qualification->person_type === 'student') {
            return $user->hasAbility('students.view', $qualification->person);
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user)
    {
        return $user->hasAbility('users.create') || $user->hasAbility('students.create');
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
        $qualification->loadMissing('person');

        if ($qualification->person_type === 'user') {
            return $user->hasAbility('users.update');
        }

        if ($qualification->person_type === 'student') {
            return $user->hasAbility('students.update', $qualification->person);
        }

        return false;
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
        $qualification->loadMissing('person');

        if ($qualification->person_type === 'user') {
            return $user->hasAbility('users.delete');
        }

        if ($qualification->person_type === 'student') {
            return $user->hasAbility('students.delete', $qualification->person);
        }

        return false;
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
