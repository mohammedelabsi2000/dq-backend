<?php

namespace App\Concerns;

use App\Contracts\BelongsToHierarchy;
use App\Models\Role;
use Illuminate\Database\Eloquent\Model;

trait HasRoles
{
    public function roles()
    {
        return $this->morphToMany(Role::class, 'authorizable', 'role_user')
            ->withPivot('scope_id', 'scope_type');
    }

    public function assignRole(Role $role, ?Model $scope = null): void
    {
        $alreadyAssigned = $this->roles()
            ->wherePivot('role_id', $role->id)
            ->wherePivot('scope_id', $scope?->id)
            ->wherePivot('scope_type', $scope ? get_class($scope) : null)
            ->exists();

        if ($alreadyAssigned) {
            return;
        }

        $this->roles()->attach($role->id, [
            'scope_id'   => $scope?->id,
            'scope_type' => $scope ? get_class($scope) : null,
        ]);
    }

    public function syncRole(Role $role, ?Model $scope = null, ?Role $newRole = null, ?Model $newScope = null): void
    {
        // احذف القديم
        $this->removeRole($role, $scope);

        // أضف الجديد
        $this->assignRole($newRole ?? $role, $newScope ?? $scope);
    }

    public function removeRole(Role $role, ?Model $scope = null): void
    {
        $this->roles()
            ->wherePivot('scope_id', $scope?->id)
            ->wherePivot('scope_type', $scope ? get_class($scope) : null)
            ->detach($role->id);
    }

    public function isGlobalAdmin(): bool
    {
        $this->loadMissing('roles');

        return $this->roles->contains(
            fn($role) => $role->pivot->scope_id === null
        );
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
                return true;
            }

            // نتحقق أن الـ scope موجود في هرمية الـ resource
            return collect($resource->getHierarchyIds())
                ->contains(
                    fn($level) =>
                    $level['id']   == $role->pivot->scope_id
                        &&
                        $level['type'] == $role->pivot->scope_type
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
