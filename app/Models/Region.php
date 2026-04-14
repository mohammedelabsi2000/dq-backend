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

        // توسيع من center للأعلى فقط
        if ($centerIds->isNotEmpty()) {
            $regionIds = $regionIds->merge(
                Center::whereIn('id', $centerIds)->pluck('region_id')
            )->unique();
        }

        // توسيع من branch للأسفل فقط إذا ما عنده region scope
        if ($branchIds->isNotEmpty() && $regionIds->isEmpty()) {
            $regionIds = $regionIds->merge(
                Region::whereIn('branch_id', $branchIds)->pluck('id')
            )->unique();
        }

        return $query->where(function (Builder $q) use ($branchIds, $regionIds) {
            if ($regionIds->isNotEmpty()) {
                $q->orWhereIn('id', $regionIds);
            }
            if ($branchIds->isNotEmpty() && $regionIds->isEmpty()) {
                $q->orWhereIn('branch_id', $branchIds);
            }
            if ($branchIds->isEmpty() && $regionIds->isEmpty()) {
                $q->whereRaw('1 = 0');
            }
        });
    }
}
