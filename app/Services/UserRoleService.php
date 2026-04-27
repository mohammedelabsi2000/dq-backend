<?php

namespace App\Services;

use App\Models\User;
use App\Models\UserScope;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;

class UserRoleService
{
    /**
     * مزامنة الأدوار والـ scopes معاً
     */
    public function assignRolesWithScopes(User $user, array $roleIds, array $scopes = []): void
    {
        DB::transaction(function () use ($user, $roleIds, $scopes) {

            // 1. مزامنة الأدوار عبر Spatie
            $roles = Role::whereIn('id', $roleIds)
                ->where('guard_name', 'sanctum')
                ->get();

            $user->syncRoles($roles);

            // 2. مزامنة الـ scopes مع التواريخ
            if (!empty($scopes)) {
                $this->syncScopes($user, $scopes);
            }
        });
    }

    /**
     * مزامنة الـ scopes مع الحفاظ على التاريخ
     */
    private function syncScopes(User $user, array $scopes): void
    {
        $newScopes = collect($scopes)->map(fn($s) => [
            'scope_type' => $s['type'],
            'scope_id'   => (int) $s['id'],
        ])->toArray();

        // 1. أغلق الـ scopes اللي ما عادت موجودة
        $user->scopes()
            ->whereNull('to_date')
            ->get()
            ->each(function ($existing) use ($newScopes) {
                $stillExists = collect($newScopes)->contains(
                    fn($s) =>
                    $s['scope_type'] === $existing->scope_type &&
                        $s['scope_id'] === (int) $existing->scope_id
                );

                if (!$stillExists) {
                    $existing->update(['to_date' => now()]);
                }
            });

        // 2. أضف الـ scopes الجديدة فقط
        foreach ($newScopes as $scope) {
            $exists = $user->scopes()
                ->where('scope_type', $scope['scope_type'])
                ->where('scope_id', $scope['scope_id'])
                ->whereNull('to_date')
                ->exists();

            if (!$exists) {
                $user->scopes()->create([
                    'scope_type' => $scope['scope_type'],
                    'scope_id'   => $scope['scope_id'],
                    'from_date' => now(),
                    'to_date'   => null,
                ]);
            }
        }
    }
}
