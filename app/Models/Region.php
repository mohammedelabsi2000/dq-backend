<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Concerns\HasVisibilityScope;

class Region extends Model
{
    use HasFactory, HasVisibilityScope;

    protected $fillable = ['name', 'branch_id', 'notes'];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }


    public function mosques()
    {
        return $this->hasMany(Mosque::class);
    }

    public function centers()
    {
        return $this->hasMany(Center::class);
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isGlobalAdmin()) {
            return $query;
        }

        $branchIds = $user->getScopeIds('branch');
        $regionIds = $user->getScopeIds('region');
        $centerIds = $user->getScopeIds('center');

        // توسيع من center للأعلى
        if ($centerIds->isNotEmpty()) {
            $regionIds = $regionIds->merge(
                Center::whereIn('id', $centerIds)->pluck('region_id')
            )->unique();
        }

        return $query->where(function (Builder $q) use ($branchIds, $regionIds) {
            if ($branchIds->isNotEmpty()) {
                $q->orWhereIn('branch_id', $branchIds);
            }
            if ($regionIds->isNotEmpty()) {
                $q->orWhereIn('id', $regionIds);
            }
            if ($branchIds->isEmpty() && $regionIds->isEmpty()) {
                $q->whereRaw('1 = 0');
            }
        });
    }
}
