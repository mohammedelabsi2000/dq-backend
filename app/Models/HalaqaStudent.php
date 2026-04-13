<?php

namespace App\Models;

use App\Concerns\HasVisibilityScope;
use App\Traits\Searchable;
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

        // توسيع الهرمية
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

        $allHalaqaIds = $halaqaIds
            ->merge(
                Halaqa::where('reference_type', 'region')
                    ->whereIn('reference_id', $regionIds)
                    ->pluck('id')
            )
            ->merge(
                Halaqa::where('reference_type', 'center')
                    ->whereIn('reference_id', $centerIds)
                    ->pluck('id')
            )
            ->unique();

        if ($allHalaqaIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->whereIn('halaqa_id', $allHalaqaIds);
    }
}
