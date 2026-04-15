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
     * @param  \App\Models\PersonalCourse  $personalCourse
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, PersonalCourse $course)
    {
        $course->loadMissing('person');

        // صاحب الدورة يشوفها دائماً
        if (
            $course->person_type === 'user' &&
            $course->person_id === $user->id
        ) {
            return true;
        }

        if ($course->person_type === 'user') {
            return $user->hasPermissionTo('users.certificates.show');
        }

        if ($course->person_type === 'student') {
            return $user->hasPermissionTo('students.certificates.show', $course->person);
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
        return $user->hasPermissionTo('users.certificates.update') || $user->hasPermissionTo('students.certificates.update');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PersonalCourse  $personalCourse
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, PersonalCourse $course)
    {
        $course->loadMissing('person');

        // صاحب الدورة يشوفها دائماً
        // if (
        //     $course->person_type === 'user' &&
        //     $course->person_id === $user->id
        // ) {
        //     return true;
        // }

        if ($course->person_type === 'user') {
            return $user->hasPermissionTo('users.certificates.update');
        }

        if ($course->person_type === 'student') {
            return $user->hasPermissionTo('students.certificates.update', $course->person);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PersonalCourse  $personalCourse
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, PersonalCourse $course)
    {
        $course->loadMissing('person');

        // صاحب الدورة يشوفها دائماً
        // if (
        //     $course->person_type === 'user' &&
        //     $course->person_id === $user->id
        // ) {
        //     return true;
        // }

        if ($course->person_type === 'user') {
            return $user->hasPermissionTo('users.certificates.delete');
        }

        if ($course->person_type === 'student') {
            return $user->hasPermissionTo('students.certificates.delete', $course->person);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\PersonalCourse  $personalCourse
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore($user, PersonalCourse $personalCourse)
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
