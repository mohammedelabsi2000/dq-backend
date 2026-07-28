<?php

namespace App\Services;

use App\Models\Center;
use App\Models\Halaqa;
use App\Models\Region;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserRoleService
{
    public function assignRolesWithScopes(User $user, array $roleIds, array $scopes = []): void
    {
        DB::transaction(function () use ($user, $roleIds, $scopes) {

            // 1. مزامنة الأدوار عبر Spatie
            $roles = Role::whereIn('id', $roleIds)
                ->where('guard_name', 'sanctum')
                ->get();

            $user->syncRoles($roles);

            // 2. مزامنة الـ scopes — دائماً حتى لو فارغة
            $this->syncScopes($user, $scopes, $roleIds);
        });
    }

    private function syncScopes(User $user, array $scopes, array $roleIds): void
    {
        $newScopes = [];
        foreach ($roleIds as $roleId) {
            foreach ($scopes as $scope) {
                $newScopes[] = [
                    'role_id'    => (int) $roleId,
                    'scope_type' => $scope['type'],
                    'scope_id'   => (int) $scope['id'],
                ];
            }
        }

        // ✅ إزالة المكررة فقط إذا في scopes
        if (!empty($newScopes)) {
            $newScopes = $this->removeRedundantParentScopes($newScopes);
        }

        // ✅ أغلق كل القديمة دائماً
        $user->scopes()
            ->whereNull('to_date')
            ->get()
            ->each(function ($existing) use ($newScopes) {
                $stillExists = collect($newScopes)->contains(
                    fn($s) =>
                    (int) $s['role_id']  === (int) $existing->role_id &&
                        $s['scope_type']     === $existing->scope_type &&
                        (int) $s['scope_id'] === (int) $existing->scope_id
                );

                if (!$stillExists) {
                    $existing->update(['to_date' => now()->endOfDay()]);
                }
            });

        // أضف الجديدة فقط إذا في scopes
        foreach ($newScopes as $scope) {
            $activeExists = $user->scopes()
                ->where('role_id',    $scope['role_id'])
                ->where('scope_type', $scope['scope_type'])
                ->where('scope_id',   $scope['scope_id'])
                ->whereNull('to_date')
                ->exists();

            if ($activeExists) {
                continue;
            }

            $user->scopes()->create([
                'role_id'    => $scope['role_id'],
                'scope_type' => $scope['scope_type'],
                'scope_id'   => $scope['scope_id'],
                'from_date'  => now(),
                'to_date'    => null,
            ]);
        }
    }

    /**
     * إزالة الـ scopes الأعلى التي يغطيها scope أدنى منها
     *
     * الهرم: branch → region → center → halaqa
     *
     * إذا أرسل المستخدم branch + region تابعة له → احذف branch
     * إذا أرسل region + center تابع لها → احذف region
     * إذا أرسل center + halaqa تابعة له → احذف center
     * إذا halaqa تابعة لـ region مباشرة → احذف region أيضاً
     */
    private function removeRedundantParentScopes(array $scopes): array
    {
        $collection = collect($scopes);

        $regionIds = $collection->where('scope_type', 'region')->pluck('scope_id');
        $centerIds = $collection->where('scope_type', 'center')->pluck('scope_id');
        $halaqaIds = $collection->where('scope_type', 'halaqa')->pluck('scope_id');

        // الفروع التي تغطيها المناطق الموجودة
        $coveredBranchIds = collect();
        if ($regionIds->isNotEmpty()) {
            $coveredBranchIds = Region::whereIn('id', $regionIds)
                ->pluck('branch_id')
                ->unique();
        }

        // المناطق التي تغطيها المراكز الموجودة
        $coveredRegionIds = collect();
        if ($centerIds->isNotEmpty()) {
            $coveredRegionIds = Center::whereIn('id', $centerIds)
                ->pluck('region_id')
                ->unique();
        }

        // المراكز والمناطق التي تغطيها الحلقات الموجودة
        // (الحلقة ممكن تكون تابعة لـ center أو region مباشرة)
        $coveredCenterIds           = collect();
        $coveredRegionIdsFromHalaqa = collect();

        if ($halaqaIds->isNotEmpty()) {
            $halaqas = Halaqa::whereIn('id', $halaqaIds)
                ->get(['reference_type', 'reference_id']);

            // // حلقات تابعة لمركز → يغطي المركز
            // $coveredCenterIds = $halaqas
            //     ->where('reference_type', 'center')
            //     ->pluck('reference_id')
            //     ->unique();

            // // حلقات تابعة لمنطقة مباشرة → يغطي المنطقة
            // $coveredRegionIdsFromHalaqa = $halaqas
            //     ->where('reference_type', 'region')
            //     ->pluck('reference_id')
            //     ->unique();

            $coveredCenterIds = $halaqas
                ->filter(fn($h) => $h->reference_type->value === 'center')  // ← استخدم ->value
                ->pluck('reference_id')
                ->unique();

            $coveredRegionIdsFromHalaqa = $halaqas
                ->filter(fn($h) => $h->reference_type->value === 'region')  // ← استخدم ->value
                ->pluck('reference_id')
                ->unique();
        }

        // دمج المناطق المغطاة من المراكز + من الحلقات المباشرة
        $allCoveredRegionIds = $coveredRegionIds
            ->merge($coveredRegionIdsFromHalaqa)
            ->unique();

        // الفروع المغطاة من المراكز (عبر مناطقها)
        $coveredBranchIdsFromCenters = collect();
        if ($coveredRegionIds->isNotEmpty()) {
            $coveredBranchIdsFromCenters = Region::whereIn('id', $coveredRegionIds)
                ->pluck('branch_id')
                ->unique();
        }

        $allCoveredBranchIds = $coveredBranchIds
            ->merge($coveredBranchIdsFromCenters)
            ->unique();

        return $collection->filter(function ($scope) use (
            $allCoveredBranchIds,
            $allCoveredRegionIds,
            $coveredCenterIds
        ) {
            // احذف الفرع إذا عنده منطقة/مركز/حلقة تغطيه
            if ($scope['scope_type'] === 'branch' && $allCoveredBranchIds->contains($scope['scope_id'])) {
                return false;
            }

            // احذف المنطقة إذا عنده مركز/حلقة يغطيها
            if ($scope['scope_type'] === 'region' && $allCoveredRegionIds->contains($scope['scope_id'])) {
                return false;
            }

            // احذف المركز إذا عنده حلقة تغطيه
            if ($scope['scope_type'] === 'center' && $coveredCenterIds->contains($scope['scope_id'])) {
                return false;
            }

            return true;
        })->values()->toArray();
    }
}
