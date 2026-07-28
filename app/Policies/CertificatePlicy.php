<?php

namespace App\Policies;

use App\Models\Certificate;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Relations\Relation;

class CertificatePlicy
{
    use HandlesAuthorization;

    public function viewAny(User $user)
    {
        return $user->hasPermissionTo('users.certificates.show')
            || $user->hasPermissionTo('students.certificates.show');
    }

    /**
     * Determine whether the user can view all certificates belonging to a specific person.
     */
    public function viewForPerson(User $user, string $personType, int $personId): bool
    {
        return ($user->hasPermissionTo('users.certificates.show') || $user->hasPermissionTo('students.certificates.show'))
            && $this->isPersonVisible($user, $personType, $personId);
    }

    public function view(User $user, Certificate $certificate)
    {
        return ($user->hasPermissionTo('users.certificates.show') || $user->hasPermissionTo('students.certificates.show'))
            && $this->isPersonVisible($user, $certificate->person_type, $certificate->person_id);
    }

    public function create(User $user, ?string $personType = null, ?int $personId = null)
    {
        if (!$user->hasPermissionTo('users.certificates.update') && !$user->hasPermissionTo('students.certificates.update')) {
            return false;
        }

        return $this->isPersonVisible($user, $personType, $personId);
    }

    public function update(User $user, Certificate $certificate)
    {
        return ($user->hasPermissionTo('users.certificates.update') || $user->hasPermissionTo('students.certificates.update'))
            && $this->isPersonVisible($user, $certificate->person_type, $certificate->person_id);
    }

    public function delete(User $user, Certificate $certificate)
    {
        return ($user->hasPermissionTo('users.certificates.update') || $user->hasPermissionTo('students.certificates.update'))
            && $this->isPersonVisible($user, $certificate->person_type, $certificate->person_id);
    }

    /**
     * يتحقق أن الشخص (مستخدم أو طالب) المرتبط بالشهادة ضمن نطاق المستخدم الحالي،
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

    public function restore(User $user)
    {
        //
    }

    public function forceDelete(User $user, Certificate $certificate)
    {
        //
    }
}
