<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Concerns\HasVisibilityScope;
use App\Models\Scopes\GenderVisibilityScope;

class Center extends Model
{
    use HasFactory, SoftDeletes, HasVisibilityScope;

    public static $usesAudit = true;

    protected $fillable = ['name', 'notes', 'region_id', 'mosque_id', 'gender', 'created_by', 'updated_by', 'deleted_by'];

    protected static function booted()
    {
        static::addGlobalScope(new GenderVisibilityScope);
    }

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
        $user = auth()->user();
        $halaqas = $this->hasMany(Halaqa::class);
        if (!$user->can('gender_visibility')) {
            $halaqas = $halaqas->where('gender', $user->gender);
        }
        return $halaqas;
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

        // محفظ حلقة ← يرى مركز حلقته فقط (إن كانت حلقته مرتبطة بمركز، وإلا فلا شيء)
        if ($halaqaIds->isNotEmpty()) {
            $centerIdsFromHalaqa = Halaqa::whereIn('id', $halaqaIds)
                ->select('reference_type', 'reference_id')
                ->get()
                ->filter(fn ($h) => $h->reference_type?->value === 'center')
                ->pluck('reference_id')
                ->unique();

            return $centerIdsFromHalaqa->isEmpty()
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('id', $centerIdsFromHalaqa);
        }

        // مدير مركز ← مراكزه فقط
        if ($centerIds->isNotEmpty()) {
            return $query->whereIn('id', $centerIds);
        }

        // مدير منطقة ← مراكز منطقته
        if ($regionIds->isNotEmpty()) {
            return $query->whereIn('region_id', $regionIds);
        }

        // مدير فرع ← يوسع لمناطق الفرع ثم مراكزها
        if ($branchIds->isNotEmpty()) {
            $regionIds = Region::whereIn('branch_id', $branchIds)->pluck('id');

            return $regionIds->isEmpty()
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('region_id', $regionIds);
        }

        return $query->whereRaw('1 = 0');
    }
}
