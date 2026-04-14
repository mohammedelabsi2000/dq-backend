<?php

namespace App\Concerns;

use App\Models\UserScope;
use Illuminate\Support\Collection;

trait HasVisibilityScope
{
    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function scopes()
    {
        return $this->hasMany(UserScope::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Checks
    |--------------------------------------------------------------------------
    */

    // مستخدم بدون scopes = مدير عام يشوف الكل
    public function isGlobalAdmin(): bool
    {
        return $this->scopes()->doesntExist();
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    public function getScopeIds(string $type): Collection
    {
        return $this->scopes()
            ->where('scope_type', $type)
            ->pluck('scope_id');
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
        // $scopes = [['type' => 'branch', 'id' => 5], ...]
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
