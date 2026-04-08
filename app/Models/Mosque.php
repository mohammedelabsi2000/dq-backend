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
            'branch' => function (Builder $q, int $branchId) {
                $q->whereHas('region', function (Builder $subQ) use ($branchId) {
                    $subQ->where('branch_id', $branchId);
                });
            },
            'region' => 'region_id',
            'mosque' => 'id',
            'center' => function (Builder $q, int $centerId) {
                // Allow center managers to see mosques in their region
                $q->orWhereHas('region.centers', function (Builder $subQ) use ($centerId) {
                    $subQ->where('id', $centerId);
                });
            },
        ]);
    }

    public function getHierarchyIds(): array
    {
        $this->loadMissing('region');

        $hierarchy = [
            ['id' => $this->id, 'type' => 'mosque'],
        ];

        if ($this->region) {
            $hierarchy[] = ['id' => $this->region_id, 'type' => 'region'];
            if ($this->region->branch_id) {
                $hierarchy[] = ['id' => $this->region->branch_id, 'type' => 'branch'];
            }
        }

        return $hierarchy;
    }
}
