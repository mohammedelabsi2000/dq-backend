<?php

namespace App\Policies;

use App\Models\PersonalCourse;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Relations\Relation;

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
    public function view($user, PersonalCourse $personalCourse)
    {
        return ($user->hasPermissionTo('users.certificates.show') || $user->hasPermissionTo('students.certificates.show'))
            && $this->isPersonVisible($user, $personalCourse->person_type, $personalCourse->person_id);
    }

    /**
     * Determine whether the user can view all courses belonging to a specific person.
     */
    public function viewForPerson($user, string $personType, int $personId): bool
    {
        return ($user->hasPermissionTo('users.certificates.show') || $user->hasPermissionTo('students.certificates.show'))
            && $this->isPersonVisible($user, $personType, $personId);
    }

    /**
     * Determine whether the user can create models.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function create($user, ?string $personType = null, ?int $personId = null)
    {
        if (!$user->hasPermissionTo('users.certificates.update') && !$user->hasPermissionTo('students.certificates.update')) {
            return false;
        }

        return $this->isPersonVisible($user, $personType, $personId);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, PersonalCourse $personalCourse)
    {
        return ($user->hasPermissionTo('users.certificates.update') || $user->hasPermissionTo('students.certificates.update'))
            && $this->isPersonVisible($user, $personalCourse->person_type, $personalCourse->person_id);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function delete($user, PersonalCourse $personalCourse)
    {
        return ($user->hasPermissionTo('users.certificates.update') || $user->hasPermissionTo('students.certificates.update'))
            && $this->isPersonVisible($user, $personalCourse->person_type, $personalCourse->person_id);
    }

    private function isPersonVisible(User $user, ?string $personType, ?int $personId): bool
    {
        if ($user->isGlobalAdmin() || !$personType || !$personId) {
            return $user->isGlobalAdmin();
        }

        $modelClass = Relation::getMorphedModel($personType) ?? $personType;

        if (!$modelClass || !class_exists($modelClass) || !method_exists($modelClass, 'scopeVisibleTo')) {
            return false;
        }

        return $modelClass::visibleTo($user)->where('id', $personId)->exists();
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
