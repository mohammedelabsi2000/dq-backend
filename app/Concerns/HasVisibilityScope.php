<?php

namespace App\Concerns;

use App\Models\UserScope;
use Illuminate\Support\Collection;

trait HasVisibilityScope
{
    public function scopes()
    {
        return $this->hasMany(UserScope::class);
    }

    public function isGlobalAdmin(): bool
    {
        return $this->scopes()->active()->doesntExist();
    }

    /**
     * جلب الـ scope IDs النشطة حسب النوع
     * الآن تجمع من كل الأدوار المرتبطة بالمستخدم
     */
    public function getScopeIds(string $type): Collection
    {
        return $this->scopes()
            ->where('scope_type', $type)
            ->active()
            ->pluck('scope_id')
            ->unique();    // ← مهم لأن نفس الـ scope قد يتكرر عبر أدوار مختلفة
    }

    /**
     * جلب الـ scope IDs النشطة حسب النوع والدور
     * للاستخدام عند الحاجة للتحقق من دور محدد
     */
    public function getScopeIdsByRole(string $type, int $roleId): Collection
    {
        return $this->scopes()
            ->where('scope_type', $type)
            ->where('role_id', $roleId)
            ->active()
            ->pluck('scope_id')
            ->unique();
    }

    public function assignScope(string $scopeType, int $scopeId): void
    {
        UserScope::firstOrCreate([
            'user_id'    => $this->id,
            'scope_type' => $scopeType,
            'scope_id'   => $scopeId,
        ]);
    }

    public function removeScope(string $scopeType, int $scopeId): void
    {
        UserScope::where('user_id', $this->id)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->delete();
    }

    public function syncScopes(array $scopes): void
    {
        UserScope::where('user_id', $this->id)->delete();

        foreach ($scopes as $scope) {
            $this->assignScope($scope['type'], $scope['id']);
        }
    }

    public function clearScopes(): void
    {
        UserScope::where('user_id', $this->id)->delete();
    }
}
