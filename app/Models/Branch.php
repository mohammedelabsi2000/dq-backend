<?php

namespace App\Models;

use App\Concerns\HasHierarchyScope;
use App\Contracts\BelongsToHierarchy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;


class Branch extends Model implements BelongsToHierarchy
{
    use HasFactory, HasHierarchyScope;

    protected $fillable = [
        'name',
        'notes',
    ];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $this->applyVisibleTo($query, $user, [
            'branch' => 'id',
        ]);
    }

    // Branch.php
    // public function scopeVisibleTo(Builder $query, User $user): Builder
    // {
    //     $user->loadMissing('roles');

    //     $hasGlobalRole = $user->roles->contains(fn($role) => $role->pivot->scope_id === null);

    //     if ($hasGlobalRole) {
    //         return $query;
    //     }

    //     $morphAlias = array_search(static::class, Relation::morphMap()) ?: static::class;

    //     $allowedIds = $user->roles
    //         ->filter(fn($role) => $role->pivot->scope_type === $morphAlias)
    //         ->pluck('pivot.scope_id');
    //     // dd($allowedIds);

    //     return $query->whereIn('id', $allowedIds);
    // }

    public function getHierarchyIds(): array
    {
        return [
            ['id' => $this->id, 'type' => 'branch'],
        ];
    }
}
