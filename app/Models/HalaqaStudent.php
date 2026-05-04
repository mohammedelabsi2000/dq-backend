<?php

namespace App\Models;

use App\Concerns\HasVisibilityScope;
use App\Concerns\Searchable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HalaqaStudent extends Model
{

    use HasFactory, SoftDeletes, Searchable, HasVisibilityScope;

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    protected $table = 'halaqa_students';

    /**
     * الأعمدة القابلة للتعبئة
     */
    protected $fillable = [
        'halaqa_id',
        'student_id',
        'from_date',
        'to_date',
        'enrollment_status_id',
    ];

    /**
     * علاقة مع جدول Halaqa
     */
    public function halaqa()
    {
        return $this->belongsTo(Halaqa::class, 'halaqa_id');
    }

    /**
     * علاقة مع جدول Student
     */
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * علاقة مع جدول Status
     */
    public function enrollment_status()
    {
        return $this->belongsTo(Constant::class, 'enrollment_status_id');
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

        // محفظ حلقة ← سجلات حلقته فقط
        if ($halaqaIds->isNotEmpty()) {
            return $query->whereIn('halaqa_id', $halaqaIds);
        }

        // مدير مركز ← سجلات حلقات مركزه
        if ($centerIds->isNotEmpty()) {
            $allHalaqaIds = Halaqa::where('reference_type', 'center')
                ->whereIn('reference_id', $centerIds)
                ->pluck('id');

            return $allHalaqaIds->isEmpty()
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('halaqa_id', $allHalaqaIds);
        }

        // مدير منطقة ← سجلات حلقات منطقته (مباشرة + عبر مراكزها)
        if ($regionIds->isNotEmpty()) {
            $centerIds = Center::whereIn('region_id', $regionIds)->pluck('id');

            $allHalaqaIds = Halaqa::where(function ($q) use ($regionIds, $centerIds) {
                $q->where(function ($q) use ($regionIds) {
                    $q->where('reference_type', 'region')
                        ->whereIn('reference_id', $regionIds);
                });
                if ($centerIds->isNotEmpty()) {
                    $q->orWhere(function ($q) use ($centerIds) {
                        $q->where('reference_type', 'center')
                            ->whereIn('reference_id', $centerIds);
                    });
                }
            })->pluck('id');

            return $allHalaqaIds->isEmpty()
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('halaqa_id', $allHalaqaIds);
        }

        // مدير فرع ← يوسع لمناطق الفرع ثم حلقاتها
        if ($branchIds->isNotEmpty()) {
            $regionIds  = Region::whereIn('branch_id', $branchIds)->pluck('id');
            $centerIds  = Center::whereIn('region_id', $regionIds)->pluck('id');

            $allHalaqaIds = Halaqa::where(function ($q) use ($regionIds, $centerIds) {
                $q->where(function ($q) use ($regionIds) {
                    $q->where('reference_type', 'region')
                        ->whereIn('reference_id', $regionIds);
                });
                if ($centerIds->isNotEmpty()) {
                    $q->orWhere(function ($q) use ($centerIds) {
                        $q->where('reference_type', 'center')
                            ->whereIn('reference_id', $centerIds);
                    });
                }
            })->pluck('id');

            return $allHalaqaIds->isEmpty()
                ? $query->whereRaw('1 = 0')
                : $query->whereIn('halaqa_id', $allHalaqaIds);
        }

        return $query->whereRaw('1 = 0');
    }
}
