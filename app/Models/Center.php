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

        // توسيع من branch للأسفل
        if ($branchIds->isNotEmpty()) {
            $regionIds = $regionIds->merge(
                Region::whereIn('branch_id', $branchIds)->pluck('id')
            )->unique();
        }

        if ($regionIds->isNotEmpty()) {
            $centerIds = $centerIds->merge(
                Center::whereIn('region_id', $regionIds)->pluck('id')
            )->unique();
        }

        if ($centerIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id', $centerIds);
    }
}
