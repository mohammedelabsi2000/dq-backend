<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;


class Mosque extends Model
{
    use HasFactory, SoftDeletes;

    public static $usesAudit = true;

    protected $fillable = ['name', 'notes', 'region_id', 'created_by', 'updated_by', 'deleted_by'];

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
        if ($user->isGlobalAdmin()) {
            return $query;
        }

        $branchIds = $user->getScopeIds('branch');
        $regionIds = $user->getScopeIds('region');
        $centerIds = $user->getScopeIds('center');
        $halaqaIds = $user->getScopeIds('halaqa');

        // محفظ حلقة ← يرى مسجد مركز حلقته فقط (إن كانت حلقته مرتبطة بمركز له مسجد، وإلا فلا شيء)
        if ($halaqaIds->isNotEmpty()) {
            $centerIdsFromHalaqa = Halaqa::whereIn('id', $halaqaIds)
                ->select('reference_type', 'reference_id')
                ->get()
                ->filter(fn ($h) => $h->reference_type?->value === 'center')
                ->pluck('reference_id')
                ->unique();

            $mosqueIds = Center::whereIn('id', $centerIdsFromHalaqa)
                ->whereNotNull('mosque_id')
                ->pluck('mosque_id');

            return $mosqueIds->isEmpty()
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('id', $mosqueIds);
        }

        // مدير مركز ← مسجد مركزه فقط
        if ($centerIds->isNotEmpty()) {
            $mosqueIds = Center::whereIn('id', $centerIds)
                ->whereNotNull('mosque_id')
                ->pluck('mosque_id');

            return $mosqueIds->isEmpty()
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('id', $mosqueIds);
        }

        // مدير منطقة ← مساجد منطقته
        if ($regionIds->isNotEmpty()) {
            return $query->whereIn('region_id', $regionIds);
        }

        // مدير فرع ← يوسع لمناطق الفرع ثم مساجدها
        if ($branchIds->isNotEmpty()) {
            $regionIds = Region::whereIn('branch_id', $branchIds)->pluck('id');

            return $regionIds->isEmpty()
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('region_id', $regionIds);
        }

        return $query->whereRaw('1 = 0');
    }
}
