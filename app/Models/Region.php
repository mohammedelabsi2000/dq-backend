<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Concerns\HasVisibilityScope;

class Region extends Model
{
    use HasFactory, SoftDeletes, HasVisibilityScope;

    public static $usesAudit = true;

    protected $fillable = ['name', 'branch_id', 'notes', 'created_by', 'updated_by', 'deleted_by'];

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
        $user = auth()->user();
        $centers = $this->hasMany(Center::class);
        if (!$user->can('gender_visibility')) {
            $centers = $centers->where('gender', $user->gender);
        }
        return $centers;
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

        // محفظ حلقة ← يرى منطقة حلقته فقط للاطلاع
        if ($halaqaIds->isNotEmpty()) {
            $halaqas = Halaqa::whereIn('id', $halaqaIds)
                ->select('reference_type', 'reference_id')
                ->get();

            $resolvedRegionIds = collect();

            $fromRegion = $halaqas->where('reference_type', 'region')->pluck('reference_id');
            $fromCenter = $halaqas->where('reference_type', 'center')->pluck('reference_id');

            if ($fromRegion->isNotEmpty()) {
                $resolvedRegionIds = $resolvedRegionIds->merge($fromRegion);
            }

            if ($fromCenter->isNotEmpty()) {
                $resolvedRegionIds = $resolvedRegionIds->merge(
                    Center::whereIn('id', $fromCenter)->pluck('region_id')
                );
            }

            $resolvedRegionIds = $resolvedRegionIds->unique();

            return $resolvedRegionIds->isEmpty()
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('id', $resolvedRegionIds);
        }

        // مدير مركز ← يرى منطقة مركزه فقط
        if ($centerIds->isNotEmpty()) {
            $resolvedRegionIds = Center::whereIn('id', $centerIds)
                ->pluck('region_id')
                ->unique();

            return $resolvedRegionIds->isEmpty()
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('id', $resolvedRegionIds);
        }

        // مدير منطقة ← مناطقه فقط
        if ($regionIds->isNotEmpty()) {
            return $query->whereIn('id', $regionIds);
        }

        // مدير فرع ← كل مناطق فرعه
        if ($branchIds->isNotEmpty()) {
            return $query->whereIn('branch_id', $branchIds);
        }

        return $query->whereRaw('1 = 0');
    }
}
