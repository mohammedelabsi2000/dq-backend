<?php

namespace App\Models;

use App\Concerns\HasHierarchyScope;
use App\Contracts\BelongsToHierarchy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mosque extends Model implements BelongsToHierarchy
{
    use HasFactory, HasHierarchyScope;

    protected $fillable = ['name', 'notes', 'region_id'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function centers()
    {
        return $this->hasMany(Center::class);
    }

    /**
     * العلاقة مع المستخدمين
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $this->applyVisibleTo($query, $user, [
            'branch' => 'branch_id',
            'region' => 'region_id',
            'mosque' => 'id',
        ]);
    }

    public function getHierarchyIds(): array
    {
        $this->loadMissing('region');

        return [
            ['id' => $this->region->branch_id, 'type' => 'branch'],
            ['id' => $this->region_id,          'type' => 'region'],
            ['id' => $this->id,                 'type' => 'mosque'],
        ];
    }

    public function getHierarchyData()
    {
        $this->loadMissing('region');

        return [
            ['id' => $this->region->branch_id, 'type' => 'branch', 'name' => $this->region->branch->name],
            ['id' => $this->region_id,          'type' => 'region', 'name' => $this->region->name],
            ['id' => $this->id,                 'type' => 'mosque', 'name' => $this->name],
        ];
    }
}
