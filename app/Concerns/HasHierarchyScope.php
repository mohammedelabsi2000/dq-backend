<?php

namespace App\Concerns;


// App\Concerns\HasHierarchyScope.php
use Illuminate\Database\Eloquent\Builder;

trait HasHierarchyScope
{
    // المنطق المشترك - كل موديل يستدعيه
    // protected function applyVisibleTo(Builder $query, $user, array $scopeMap): Builder
    // {
    //     $user->loadMissing('roles');

    //     $hasGlobalRole = $user->roles->contains(fn($role) => $role->pivot->scope_id === null);
    //     if ($hasGlobalRole) {
    //         return $query;
    //     }

    //     return $query->where(function (Builder $q) use ($user, $scopeMap) {
    //         foreach ($user->roles as $role) {
    //             $scopeType = $role->pivot->scope_type;
    //             $scopeId = $role->pivot->scope_id;

    //             if (isset($scopeMap[$scopeType])) {
    //                 $column = $scopeMap[$scopeType];
    //                 $q->orWhere($column, $scopeId);
    //             }
    //         }
    //     });
    // }

    // protected function applyVisibleTo(Builder $query, $user, array $scopeMap): Builder
    // {
    //     $user->loadMissing('roles');

    //     $hasGlobalRole = $user->roles->contains(fn($role) => $role->pivot->scope_id === null);
    //     if ($hasGlobalRole) {
    //         return $query;
    //     }

    //     $matchingRoles = $user->roles->filter(
    //         fn($role) => isset($scopeMap[$role->pivot->scope_type])
    //     );

    //     if ($matchingRoles->isEmpty()) {
    //         return $query->whereRaw('1 = 0');
    //     }

    //     return $query->where(function (Builder $q) use ($matchingRoles, $scopeMap) {
    //         foreach ($matchingRoles as $role) {
    //             $scopeType = $role->pivot->scope_type;
    //             $scopeId   = $role->pivot->scope_id;
    //             $column    = $scopeMap[$scopeType];

    //             // إذا الـ column هو subquery callable
    //             if (is_callable($column)) {
    //                 $column($q, $scopeId);
    //             } else {
    //                 $q->orWhere($column, $scopeId);
    //             }
    //         }
    //     });
    // }

    protected function applyVisibleTo(Builder $query, $user, array $scopeMap): Builder
    {
        $user->loadMissing('roles');

        $hasGlobalRole = $user->roles->contains(
            fn($role) =>
            $role->pivot->scope_id === null && $role->pivot->scope_type === null
        );

        if ($hasGlobalRole) {
            return $query;
        }

        $matchingRoles = $user->roles->filter(
            fn($role) => isset($scopeMap[$role->pivot->scope_type])
        );

        if ($matchingRoles->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($matchingRoles, $scopeMap) {
            foreach ($matchingRoles as $role) {
                $scopeType = $role->pivot->scope_type;
                $scopeId   = $role->pivot->scope_id;
                $column    = $scopeMap[$scopeType];

                // ✅ scope_id = null يعني Admin على كل هذا النوع → يرى كل شيء
                if ($scopeId === null) {
                    $q->orWhereRaw('1 = 1');
                    return; // لا داعي لبقية الشروط
                }

                if (is_callable($column)) {
                    $column($q, $scopeId);
                } else {
                    $q->orWhere($column, $scopeId);
                }
            }
        });
    }
}
