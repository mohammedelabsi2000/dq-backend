<?php

namespace App\Policies;

use App\Models\AcademicQualification;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Relations\Relation;

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
     * Determine whether the user can view all qualifications belonging to a specific person.
     */
    public function viewForPerson($user, string $personType, int $personId): bool
    {
        return ($user->hasPermissionTo('users.certificates.show') || $user->hasPermissionTo('students.certificates.show'))
            && $this->isPersonVisible($user, $personType, $personId);
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
        return ($user->hasPermissionTo('users.certificates.show') || $user->hasPermissionTo('students.certificates.show'))
            && $this->isPersonVisible($user, $qualification->person_type, $qualification->person_id);
    }

    /**
     * Determine whether user can create models.
     *
     * @param  \App\Models\User  $user
     * @param  string|null  $personType
     * @param  int|null  $personId
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
     * @param  \App\Models\AcademicQualification  $academicQualification
     * @return \Illuminate\Auth\Access\Response|bool
     */
    public function update($user, AcademicQualification $qualification)
    {
        return ($user->hasPermissionTo('users.certificates.update') || $user->hasPermissionTo('students.certificates.update'))
            && $this->isPersonVisible($user, $qualification->person_type, $qualification->person_id);
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
        return ($user->hasPermissionTo('users.certificates.update') || $user->hasPermissionTo('students.certificates.update'))
            && $this->isPersonVisible($user, $qualification->person_type, $qualification->person_id);
    }

    /**
     * يتحقق أن الشخص (مستخدم أو طالب) المرتبط بالمؤهل ضمن نطاق المستخدم الحالي،
     * لأن صلاحية الشهادات لا ترتبط بنطاق جغرافي بحد ذاتها.
     */
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
