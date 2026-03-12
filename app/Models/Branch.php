<?php

namespace App\Models;

use App\Contracts\BelongsToHierarchy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;


class Branch extends Model implements BelongsToHierarchy
{
    use HasFactory;

    protected $fillable = [
        'name',
        'notes',
    ];

    public function regions()
    {
        return $this->hasMany(Region::class);
    }

    public function scopeAllowedFor(Builder $query, User $user)
    {
        $user->loadMissing('roles');

        // إذا كان Admin (scope = null)
        if ($user->roles->where('pivot.scope_id', null)->isNotEmpty()) {
            return $query;
        }

        $branchIds = $user->roles
            ->where('pivot.scope_type', self::class)
            ->pluck('pivot.scope_id')
            ->filter();

        return $query->whereIn('id', $branchIds);
    }

    public function getHierarchyIds(): array
    {
        return [
            ['id' => $this->id, 'type' => 'branch'],
        ];
    }
    
    public function getHierarchyData()
    {
        return [
            ['id' => $this->id, 'type' => 'branch', 'name' => $this->name],
        ];
    }
}
