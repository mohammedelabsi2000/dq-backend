<?php

namespace App\Policies;

use App\Models\HalaqaStudent;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HalaqaStudentPolicy
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
        return $user->hasAbility('halaqa_students.view');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HalaqaStudent  $halaqaStudent
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function view($user, HalaqaStudent $halaqaStudent)
    {
        $halaqaStudent->loadMissing('halaqa');

        return $user->hasAbility('halaqa_students.view', $halaqaStudent->halaqa);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user)
    {
        return $user->hasAbility('halaqa_students.create');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HalaqaStudent  $halaqaStudent
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, HalaqaStudent $halaqaStudent)
    {
        $halaqaStudent->loadMissing('halaqa');

        return $user->hasAbility('halaqa_students.update', $halaqaStudent->halaqa);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HalaqaStudent  $halaqaStudent
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, HalaqaStudent $halaqaStudent)
    {
        $halaqaStudent->loadMissing('halaqa');

        return $user->hasAbility('halaqa_students.delete', $halaqaStudent->halaqa);
    }

    /**
     * Determine whether the user can restore the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HalaqaStudent  $halaqaStudent
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function restore($user, HalaqaStudent $halaqaStudent)
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     *
     * @param  \App\Models\User  $user
     * @param  \App\Models\HalaqaStudent  $halaqaStudent
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function forceDelete($user, HalaqaStudent $halaqaStudent)
    {
        //
    }
}
