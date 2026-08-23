<?php

namespace App\Policies;

use App\Models\Center;
use App\Models\Halaqa;
use App\Models\Region;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class HalaqaPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('halaqas.show');
    }

    public function view(User $user, Halaqa $halaqa): bool
    {
        return $user->hasPermissionTo('halaqas.show')
            && $this->isVisible($user, $halaqa);
    }

    public function create(User $user, ?string $referenceType = null, ?int $referenceId = null): bool
    {
        if (!$user->hasPermissionTo('halaqas.create')) {
            return false;
        }

        if ($user->isGlobalAdmin() || !$referenceType || !$referenceId) {
            return true;
        }

        return $this->isReferenceInScope($user, $referenceType, $referenceId);
    }

    public function update(User $user, Halaqa $halaqa, ?string $referenceType = null, ?int $referenceId = null): bool
    {
        if (!$user->hasPermissionTo('halaqas.update') || !$this->isVisible($user, $halaqa)) {
            return false;
        }

        // إذا تم تغيير المرجع (المركز/المنطقة)، يجب أن يكون المرجع الجديد أيضاً ضمن نطاق المستخدم
        if (!$user->isGlobalAdmin() && $referenceType && $referenceId) {
            return $this->isReferenceInScope($user, $referenceType, $referenceId);
        }

        return true;
    }

    public function delete(User $user, Halaqa $halaqa): bool
    {
        return $user->hasPermissionTo('halaqas.delete')
            && $this->isVisible($user, $halaqa);
    }

    public function restore(User $user, Halaqa $halaqa): bool
    {
        return $user->hasPermissionTo('halaqas.restore')
            && $this->isVisible($user, $halaqa);
    }

    private function isVisible(User $user, Halaqa $halaqa): bool
    {
        return Halaqa::visibleTo($user)->where('id', $halaqa->id)->exists();
    }

    private function isReferenceInScope(User $user, string $referenceType, int $referenceId): bool
    {
        return match ($referenceType) {
            'center' => Center::visibleTo($user)->where('id', $referenceId)->exists(),
            'region' => Region::visibleTo($user)->where('id', $referenceId)->exists(),
            default  => false,
        };
    }
}
