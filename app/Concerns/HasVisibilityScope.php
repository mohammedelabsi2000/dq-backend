<?php

namespace App\Concerns;

use App\Models\Center;
use App\Models\Halaqa;
use App\Models\UserScope;
use App\Support\ScopeCache;
use Illuminate\Support\Collection;

trait HasVisibilityScope
{
    public function scopes()
    {
        return $this->hasMany(UserScope::class);
    }
    
    public function isGlobalAdmin(): bool
    {
        return app(ScopeCache::class)->activeScopeIds($this)->isEmpty();
    }

    /**
     * جلب الـ scope IDs النشطة حسب النوع
     * الآن تجمع من كل الأدوار المرتبطة بالمستخدم
     */
    public function getScopeIds(string $type): Collection
    {
        $user = auth()->user();

        /*if ($user->isGlobalAdmin()) {
            switch ($type) {
                case 'branch':
                    # code...
                    break;

                default:
                    # code...
                    break;
            }
        }

        if (!$user->can('gender_visibility')) {
            switch ($type) {
                case 'center':
                    $scopeIds = Center::where('gender', $user->gender)->pluck('id');
                    break;
                case 'halaqa':
                    $scopeIds = Halaqa::where('gender', $user->gender)->pluck('id');
                    break;
                default:
                    $scopeIds = [];
            }
        }*/

        // النطاقات النشطة تُجلب مرة واحدة لكل طلب (فريدة، لأن نفس الـ scope قد يتكرر عبر أدوار مختلفة)
        return app(ScopeCache::class)->activeScopeIds($this)->get($type, collect());
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
            'user_id' => $this->id,
            'scope_type' => $scopeType,
            'scope_id' => $scopeId,
        ], [
            'from_date' => now(),
        ]);
    }

    public function removeScope(string $scopeType, int $scopeId): void
    {
        UserScope::where('user_id', $this->id)
            ->where('scope_type', $scopeType)
            ->where('scope_id', $scopeId)
            ->delete();

        // الحذف عبر الاستعلام لا يطلق أحداث الموديل، فنفرّغ الذاكرة المؤقتة يدوياً
        app(ScopeCache::class)->flush();
    }

    public function syncScopes(array $scopes): void
    {
        $this->clearScopes();

        foreach ($scopes as $scope) {
            $this->assignScope($scope['type'], $scope['id']);
        }
    }

    public function clearScopes(): void
    {
        UserScope::where('user_id', $this->id)->delete();

        app(ScopeCache::class)->flush();
    }
}
