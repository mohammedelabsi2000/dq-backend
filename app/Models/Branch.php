<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use App\Concerns\HasVisibilityScope;

class Branch extends Model
{
    use HasFactory, HasVisibilityScope;

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
        if ($user->isGlobalAdmin()) {
            return $query;
        }

        $branchIds = $user->getScopeIds('branch');
        $regionIds = $user->getScopeIds('region');
        $centerIds = $user->getScopeIds('center');
        $halaqaIds = $user->getScopeIds('halaqa');

        // توسيع الهرمية للأعلى
        if ($regionIds->isNotEmpty()) {
            $branchIds = $branchIds->merge(
                Region::whereIn('id', $regionIds)->pluck('branch_id')
            )->unique();
        }

        if ($centerIds->isNotEmpty()) {
            $branchIds = $branchIds->merge(
                Center::whereIn('id', $centerIds)
                    ->join('regions', 'centers.region_id', '=', 'regions.id')
                    ->pluck('regions.branch_id')
            )->unique();
        }

        if ($halaqaIds->isNotEmpty()) {
            $branchIds = $branchIds->merge(
                Halaqa::whereIn('id', $halaqaIds)
                    ->where('reference_type', 'region')
                    ->join('regions', 'halaqas.reference_id', '=', 'regions.id')
                    ->pluck('regions.branch_id')
            )->unique();
        }

        if ($branchIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id', $branchIds);
    }
}
