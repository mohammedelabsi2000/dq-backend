<?php

namespace App\Models;

use App\Concerns\HasHierarchyScope;
use App\Contracts\BelongsToHierarchy;
use App\Traits\Searchable;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HalaqaStudent extends Model implements BelongsToHierarchy
{

    use HasFactory, SoftDeletes, HasHierarchyScope, Searchable;

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

    public function getHierarchyIds(): array
    {
        $this->loadMissing('halaqa.reference');

        $hierarchy = [
            ['id' => $this->id, 'type' => 'halaqa_student'],
        ];

        if ($this->halaqa) {
            $hierarchy[] = ['id' => $this->halaqa->id, 'type' => 'halaqa'];

            // إضافة هرمية الحلقة
            if ($this->halaqa->reference_type === 'center') {
                $center = $this->halaqa->reference;
                if ($center) {
                    $hierarchy[] = ['id' => $center->id, 'type' => 'center'];
                    if ($center->region_id) {
                        $hierarchy[] = ['id' => $center->region_id, 'type' => 'region'];
                        if ($center->region->branch_id) {
                            $hierarchy[] = ['id' => $center->region->branch_id, 'type' => 'branch'];
                        }
                    }
                }
            } elseif ($this->halaqa->reference_type === 'region') {
                $region = $this->halaqa->reference;
                if ($region) {
                    $hierarchy[] = ['id' => $region->id, 'type' => 'region'];
                    if ($region->branch_id) {
                        $hierarchy[] = ['id' => $region->branch_id, 'type' => 'branch'];
                    }
                }
            }
        }

        return $hierarchy;
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $this->applyVisibleTo($query, $user, [
            'halaqa' => fn($q, $scopeId) => $q->orWhere('halaqa_id', $scopeId),

            'center' => fn($q, $scopeId) => $q->orWhereHas(
                'halaqa',
                fn($q) =>
                $q->where('reference_type', 'center')
                    ->where('reference_id', $scopeId)
            ),

            'region' => fn($q, $scopeId) => $q->orWhere(function ($q) use ($scopeId) {
                $q->orWhereHas(
                    'halaqa',
                    fn($q) =>
                    $q->where('reference_type', 'region')
                        ->where('reference_id', $scopeId)
                );

                $centerIds = Center::where('region_id', $scopeId)->pluck('id');
                if ($centerIds->isNotEmpty()) {
                    $q->orWhereHas(
                        'halaqa',
                        fn($q) =>
                        $q->where('reference_type', 'center')
                            ->whereIn('reference_id', $centerIds)
                    );
                }
            }),

            'branch' => fn($q, $scopeId) => $q->orWhere(function ($q) use ($scopeId) {
                $regionIds = Region::where('branch_id', $scopeId)->pluck('id');

                if ($regionIds->isNotEmpty()) {
                    $q->orWhereHas(
                        'halaqa',
                        fn($q) =>
                        $q->where('reference_type', 'region')
                            ->whereIn('reference_id', $regionIds)
                    );

                    $centerIds = Center::whereIn('region_id', $regionIds)->pluck('id');
                    if ($centerIds->isNotEmpty()) {
                        $q->orWhereHas(
                            'halaqa',
                            fn($q) =>
                            $q->where('reference_type', 'center')
                                ->whereIn('reference_id', $centerIds)
                        );
                    }
                }
            }),
        ]);
    }
}
