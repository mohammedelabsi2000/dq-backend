<?php

namespace App\Concerns;

use App\Contracts\BelongsToHierarchy;
use App\Models\Branch;
use App\Models\Region;
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
            ->wherePivot('scope_type', $scope ? $scope->getMorphClass() : null)
            ->exists();

        if ($alreadyAssigned) {
            return;
        }

        $this->roles()->attach($role->id, [
            'scope_id'   => $scope?->id,
            'scope_type' => $scope ? $scope->getMorphClass() : null,
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
            ->wherePivot('scope_type', $scope ? $scope->getMorphClass() : null)
            ->detach($role->id);
    }

    // public function hasAbility(string $ability, ?BelongsToHierarchy $resource = null): bool
    // {
    //     // dd($resource);
    //     // return true; // دائمًا يسمح
    //     $this->loadMissing('roles.roleAbilities');

    //     $roles = $this->roles->filter(function ($role) use ($resource) {
    //         // scope_id = null → admin عام صلاحيته على كل شيء
    //         if ($role->pivot->scope_id === null) {
    //             return true;
    //         }

    //         // إذا لم يكن هناك resource → نرفض الـ scoped roles
    //         if ($resource === null) {
    //             return false;
    //         }

    //         // نتحقق أن الـ scope موجود في هرمية الـ resource
    //         return collect($resource->getHierarchyIds())
    //             ->contains(
    //                 fn($level) =>
    //                 $level['id']   == $role->pivot->scope_id
    //                     &&
    //                     $level['type'] == $role->pivot->scope_type
    //             );
    //     });

    //     $abilities = $roles
    //         ->flatMap(fn($role) => $role->roleAbilities)
    //         ->where('ability', $ability);

    //     // // deny يتغلب على allow دائماً
    //     if ($abilities->where('type', 'deny')->isNotEmpty()) {
    //         return false;
    //     }

    //     return $abilities->where('type', 'allow')->isNotEmpty();
    // }
    // public function hasAbility(string $ability, ?BelongsToHierarchy $resource = null): bool
    // {
    //     // تحميل العلاقات مرة واحدة للأداء
    //     $this->loadMissing('roles.roleAbilities');

    //     $roles = $this->roles->filter(function ($role) use ($resource, $ability) {
    //         // 1. إذا كان الدور عاماً (Admin مثلاً) -> اسمح له
    //         if ($role->pivot->scope_id === null) {
    //             return true;
    //         }

    //         // 2. التعديل الجديد: إذا لم نمرر مورد (حالة viewAny)
    //         // نتحقق: هل هذا الدور (مهما كان نطاقه) يمتلك هذه الصلاحية؟
    //         if ($resource === null) {
    //             return $role->roleAbilities->contains('ability', $ability);
    //         }

    //         // 3. إذا كان هناك مورد محدد (حالة view أو update) نطبق منطق الهرمية الخاص بك
    //         return collect($resource->getHierarchyIds())
    //             ->contains(
    //                 fn($level) =>
    //                 $level['id']   == $role->pivot->scope_id &&
    //                     $level['type'] == $role->pivot->scope_type
    //             );
    //     });

    //     $abilities = $roles
    //         ->flatMap(fn($role) => $role->roleAbilities)
    //         ->where('ability', $ability);

    //     // منطق الـ deny والـ allow
    //     if ($abilities->where('type', 'deny')->isNotEmpty()) {
    //         return false;
    //     }

    //     return $abilities->where('type', 'allow')->isNotEmpty();
    // }

    public function hasAbility(string $ability, ?BelongsToHierarchy $resource = null, $resourceType = null): bool
    {
        $this->loadMissing('roles.roleAbilities');

        // $roles = $this->roles->filter(function ($role) use ($resource) {
        //     if ($role->pivot->scope_id === null) return true;
        $roles = $this->roles->filter(function ($role) use ($resource) {
            if ($role->pivot->scope_id === null && $role->pivot->scope_type === null) { // ✅
                return true;
            }

            // ✅ إذا resource = null → اقبل أي role له صلاحية (للـ viewAny)
            if ($resource === null) return true;

            return collect($resource->getHierarchyIds())
                ->contains(
                    fn($level) =>
                    $level['id']   == $role->pivot->scope_id &&
                        $level['type'] == $role->pivot->scope_type
                );
        });
        // dd($roles->first());
        $abilities = $roles
            ->flatMap(fn($role) => $role->roleAbilities)
            ->where('ability', $ability);

        if ($abilities->where('type', 'deny')->isNotEmpty()) return false;

        return $abilities->where('type', 'allow')->isNotEmpty();
    }
}
