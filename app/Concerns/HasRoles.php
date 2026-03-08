<?php

namespace App\Concerns;

use App\Contracts\BelongsToHierarchy;
use App\Models\Role;

trait HasRoles
{
    // public function roles()
    // {
    //     return $this->morphToMany(Role::class, 'authorizable', 'role_user');
    // }

    // // public function hasAbility($ability)
    // // {
    // //     return $this->roles()->whereHas('roleAbilities', function ($query) use ($ability) {
    // //         $query->where('ability', $ability)
    // //             ->where('type', '=', 'allow');
    // //     })->exists();
    // // }
    // public function hasAbility(string $ability): bool
    // {
    //     $this->loadMissing('roles.roleAbilities');

    //     $abilities = $this->roles
    //         ->flatMap(fn($role) => $role->roleAbilities)
    //         ->where('ability', $ability);

    //     // إذا وُجد deny في أي role يُرفض فوراً
    //     if ($abilities->where('type', 'deny')->isNotEmpty()) {
    //         return false;
    //     }

    //     // يجب وجود allow واحد على الأقل
    //     return $abilities->where('type', 'allow')->isNotEmpty();
    // }

    public function roles()
    {
        return $this->morphToMany(Role::class, 'authorizable', 'role_user')
            ->withPivot('scope_id', 'scope_type');
    }

    public function hasAbility(string $ability, ?BelongsToHierarchy $resource = null): bool
    {
        $this->loadMissing('roles.roleAbilities');

        $roles = $this->roles->filter(function ($role) use ($resource) {
            // scope_id = null → admin عام صلاحيته على كل شيء
            if ($role->pivot->scope_id === null) {
                return true;
            }

            // إذا لم يكن هناك resource → نرفض الـ scoped roles
            if ($resource === null) {
                return false;
            }

            // نتحقق أن الـ scope موجود في هرمية الـ resource
            return collect($resource->getHierarchyIds())
                ->contains(
                    fn($level) =>
                    $level['id']   == $role->pivot->scope_id &&
                        $level['type'] == $role->pivot->scope_type
                );
        });

        $abilities = $roles
            ->flatMap(fn($role) => $role->roleAbilities)
            ->where('ability', $ability);

        // deny يتغلب على allow دائماً
        if ($abilities->where('type', 'deny')->isNotEmpty()) {
            return false;
        }

        return $abilities->where('type', 'allow')->isNotEmpty();
    }
}
