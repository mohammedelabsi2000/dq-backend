<?php

namespace App\Models;

use App\Concerns\HasHierarchyScope;
use App\Contracts\BelongsToHierarchy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Region extends Model implements BelongsToHierarchy
{
    use HasFactory, HasHierarchyScope;

    protected $fillable = ['name', 'branch_id', 'notes'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }


    public function mosques()
    {
        return $this->hasMany(Mosque::class);
    }
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $this->applyVisibleTo($query, $user, [
            'branch' => 'branch_id',
            'region' => 'id',
        ]);
    }

    public function getHierarchyIds(): array
    {

        return [
            ['id' => $this->branch_id, 'type' => 'branch'],
            ['id' => $this->id,        'type' => 'region'],
        ];
    }

    public function getHierarchyData()
    {
        $this->loadMissing('branch');

        return [
            ['id' => $this->branch_id, 'type' => 'branch', 'name' => $this->branch->name],
            ['id' => $this->id,        'type' => 'region', 'name' => $this->name],
        ];
    }
}
