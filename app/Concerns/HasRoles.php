<?php

namespace App\Concerns;

use App\Contracts\BelongsToHierarchy;
use App\Models\Role;

trait HasRoles
{
    public function roles()
    {
        dd(response()->json(
            $this->morphToMany(Role::class, 'authorizable', 'role_user')
        ));
        return $this->morphToMany(Role::class, 'authorizable', 'role_user')
            ->withPivot('scope_id', 'scope_type');
    }
    public function hasAbility(string $ability, ?BelongsToHierarchy $resource = null): bool
    {
        // dd($resource);
        // return true; // دائمًا يسمح
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
<<<<<<< HEAD
                    $level['id']   == $role->pivot->scope_id
                        &&
                        $level['type'] == $role->pivot->scope_type
=======
                    $level['id'] == $role->pivot->scope_id &&
                    $level['type'] == $role->pivot->scope_type
>>>>>>> 10af43513f8c857882db09284cb027241097b001
                );
        });

        $abilities = $roles
            ->flatMap(fn($role) => $role->roleAbilities)
            ->where('ability', $ability);

        // // deny يتغلب على allow دائماً
        if ($abilities->where('type', 'deny')->isNotEmpty()) {
            return false;
        }

        return $abilities->where('type', 'allow')->isNotEmpty();
    }
}
