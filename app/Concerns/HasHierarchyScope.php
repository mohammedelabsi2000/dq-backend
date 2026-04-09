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

        // Get all roles that match the scope map OR are higher hierarchy roles
        $matchingRoles = $user->roles->filter(function ($role) use ($scopeMap) {
            // Direct match in scope map
            if (isset($scopeMap[$role->pivot->scope_type])) {
                return true;
            }

            // Higher hierarchy roles can see lower units
            $higherRoles = [
                'center' => ['region', 'branch'], // center managers can see regions and branches
                'region' => ['branch'], // region managers can see branches
                'branch' => [], // branch managers only see branches
            ];

            return isset($higherRoles[$role->pivot->scope_type]) &&
                in_array($this->getCurrentModelType(), $higherRoles[$role->pivot->scope_type]);
        });

        if ($matchingRoles->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($matchingRoles, $scopeMap) {
            foreach ($matchingRoles as $role) {
                $scopeType = $role->pivot->scope_type;
                $scopeId   = $role->pivot->scope_id;

                // Check if this role type is in scope map
                if (isset($scopeMap[$scopeType])) {
                    $column = $scopeMap[$scopeType];

                    // scope_id = null means Admin on this type sees everything
                    if ($scopeId === null) {
                        $q->orWhereRaw('1 = 1');
                        return; // no need for other conditions
                    }

                    if (is_callable($column)) {
                        $column($q, $scopeId);
                    } else {
                        $q->orWhere($column, $scopeId);
                    }
                } else {
                    // Handle higher hierarchy roles
                    $this->applyHigherHierarchyFilter($q, $scopeType, $scopeId);
                }
            }
        });
    }

    private function getCurrentModelType(): string
    {
        // Get the current model type from the calling context
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);

        foreach ($backtrace as $trace) {
            if (isset($trace['class']) && method_exists($trace['class'], 'getMorphClass')) {
                $model = new $trace['class'];
                if (method_exists($model, 'getMorphClass')) {
                    return $model->getMorphClass();
                }
            }
        }

        // Fallback to class name
        return strtolower(class_basename($backtrace[1]['class'] ?? ''));
    }

    private function applyHigherHierarchyFilter(Builder $query, string $managerType, int $managerId): void
    {
        switch ($managerType) {
            case 'center':
                // Center managers can see their branch
                $query->orWhereHas('regions.centers', function (Builder $q) use ($managerId) {
                    $q->where('id', $managerId);
                });
                break;
            case 'region':
                // Region managers can see their branch
                $query->orWhereHas('regions', function (Builder $q) use ($managerId) {
                    $q->where('id', $managerId);
                });
                break;
        }
    }
}
