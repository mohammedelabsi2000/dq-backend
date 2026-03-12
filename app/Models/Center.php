<?php

namespace App\Models;

use App\Concerns\HasHierarchyScope;
use App\Contracts\BelongsToHierarchy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Center extends Model implements BelongsToHierarchy
{
    use HasFactory, HasHierarchyScope;

    protected $fillable = ['name', 'notes', 'region_id', 'mosque_id'];

    public function region()
    {
        return $this->belongsTo(Region::class);
    }

    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    public function halaqat()
    {
        return $this->hasMany(Halaqa::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $this->applyVisibleTo($query, $user, [
            'branch' => fn(Builder $q, $id) => $q->orWhereHas('region', fn($r) => $r->where('branch_id', $id)),
            'region' => 'region_id',
            'center' => 'id',
        ]);
    }

    public function getHierarchyIds(): array
    {
        $this->loadMissing('region');

        return [
            ['id' => $this->region->branch_id, 'type' => 'branch'],
            ['id' => $this->region_id,          'type' => 'region'],
            ['id' => $this->id,                 'type' => 'center'],
        ];
    }
}
