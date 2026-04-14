<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Concerns\HasVisibilityScope;

class Center extends Model
{
    use HasFactory, HasVisibilityScope;

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
        if ($user->isGlobalAdmin()) {
            return $query;
        }

        $branchIds = $user->getScopeIds('branch');
        $regionIds = $user->getScopeIds('region');
        $centerIds = $user->getScopeIds('center');

        // مدير فرع ← يشوف كل مناطق الفرع ثم مراكزها
        if ($branchIds->isNotEmpty() && $regionIds->isEmpty() && $centerIds->isEmpty()) {
            $regionIds = $regionIds->merge(
                Region::whereIn('branch_id', $branchIds)->pluck('id')
            )->unique();
        }

        // مدير منطقة ← يشوف مراكز منطقته بس
        if ($regionIds->isNotEmpty()) {
            $centerIds = $centerIds->merge(
                Center::whereIn('region_id', $regionIds)->pluck('centers.id')
            )->unique();
        }

        if ($centerIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('centers.id', $centerIds);
    }
}
