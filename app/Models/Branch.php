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

        // صعود من region ← branch
        if ($regionIds->isNotEmpty()) {
            $branchIds = $branchIds->merge(
                Region::whereIn('id', $regionIds)->pluck('branch_id')
            )->unique();
        }

        // صعود من center ← region ← branch
        if ($centerIds->isNotEmpty()) {
            $branchIds = $branchIds->merge(
                Region::whereIn(
                    'id',
                    Center::whereIn('id', $centerIds)->pluck('region_id')
                )->pluck('branch_id')
            )->unique();
        }

        // صعود من halaqa ← branch (مع مراعاة reference_type)
        if ($halaqaIds->isNotEmpty()) {
            $halaqas = Halaqa::whereIn('id', $halaqaIds)
                ->select('reference_type', 'reference_id')
                ->get();

            // حلقات مرتبطة بمنطقة مباشرة
            $fromRegion = $halaqas->where('reference_type', 'region')->pluck('reference_id');

            // حلقات مرتبطة بمركز ← نصعد لمنطقته أولاً
            $fromCenter = $halaqas->where('reference_type', 'center')->pluck('reference_id');

            if ($fromRegion->isNotEmpty()) {
                $branchIds = $branchIds->merge(
                    Region::whereIn('id', $fromRegion)->pluck('branch_id')
                )->unique();
            }

            if ($fromCenter->isNotEmpty()) {
                $branchIds = $branchIds->merge(
                    Region::whereIn(
                        'id',
                        Center::whereIn('id', $fromCenter)->pluck('region_id')
                    )->pluck('branch_id')
                )->unique();
            }
        }

        if ($branchIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('id', $branchIds);
    }
}
