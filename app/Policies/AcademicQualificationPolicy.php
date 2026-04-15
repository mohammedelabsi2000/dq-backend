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
        $qualification->loadMissing('person');

        // صاحب الشهادة يشوف شهادته دائماً
        if (
            $qualification->person_type === 'user' &&
            $qualification->person_id === $user->id
        ) {
            return true;
        }

        if ($qualification->person_type === 'user') {
            return $user->hasPermissionTo('users.certificates.show');
        }

        if ($qualification->person_type === 'student') {
            return $user->hasPermissionTo('students.certificates.show', $qualification->person);
        }

        return false;
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
        // إذا تم تمرير person، تحقق من صلاحياته
        if ($person) {
            if ($person instanceof User) {
                return $user->hasPermissionTo('users.certificates.create') ||
                    ($user->id === $person->id && $user->hasPermissionTo('users.certificates.create'));
            }

            if ($person instanceof Student) {
                return $user->hasPermissionTo('students.certificates.create', $person);
            }
        }

        // خلاف ذلك، تحقق من الصلاحية العامة
        return $user->hasPermissionTo('users.certificates.create') || $user->hasPermissionTo('students.certificates.create');
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
            return $user->hasPermissionTo('users.certificates.update');
        }

        if ($qualification->person_type === 'student') {
            return $user->hasPermissionTo('students.certificates.update', $qualification->person);
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
            return $user->hasPermissionTo('users.certificates.delete');
        }

        if ($qualification->person_type === 'student') {
            return $user->hasPermissionTo('students.certificates.delete', $qualification->person);
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
