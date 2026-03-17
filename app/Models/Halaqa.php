<?php

namespace App\Models;

use App\Contracts\BelongsToHierarchy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Halaqa extends Model implements BelongsToHierarchy
{
    use HasFactory, SoftDeletes;

    protected $table = 'halaqas';

    protected $fillable = [
        'name',
        'location',
        'description',
        'reference_type',
        'reference_id',
        'type_id',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    /**
     * Polymorphic relation (reference)
     */
    public function reference()
    {
        return $this->morphTo();
    }

    /**
     * Type relation (constants table)
     */
    public function type()
    {
        return $this->belongsTo(Constant::class, 'type_id');
    }

    /**
     * Get the student enrollments for this halaqa.
     */
    public function studentEnrollments()
    {
        return $this->hasMany(HalaqaStudent::class);
    }

    /**
     * Get the students enrolled in this halaqa.
     */
    public function students()
    {
        return $this->belongsToMany(Student::class, 'halaqa_students')
            ->withPivot(['from_date', 'to_date', 'status_id'])
            ->withTimestamps();
        // ->using(HalaqaStudent::class);
    }

    public function getHierarchyIds(): array
    {
        $this->loadMissing('reference');

        // لو تابعة لـ Center
        if ($this->reference_type === 'center') {
            $center = $this->reference;
            $center->loadMissing('region');

            return [
                ['id' => $center->region->branch_id, 'type' => 'branch'],
                ['id' => $center->region_id, 'type' => 'region'],
                ['id' => $center->id, 'type' => 'center'],
                ['id' => $this->id, 'type' => 'halaqa'],
            ];
        }

        // لو تابعة لـ Region
        if ($this->reference_type === 'region') {
            $region = $this->reference;

            return [
                ['id' => $region->branch_id, 'type' => 'branch'],
                ['id' => $region->id, 'type' => 'region'],
                ['id' => $this->id, 'type' => 'halaqa'],
            ];
        }

        return [
            ['id' => $this->id, 'type' => 'halaqa'],
        ];
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $user->loadMissing('roles');

        $hasGlobalRole = $user->roles->contains(fn($role) => $role->pivot->scope_id === null);
        if ($hasGlobalRole) {
            return $query;
        }

        $matchingRoles = $user->roles->filter(
            fn($role) => in_array($role->pivot->scope_type, ['branch', 'region', 'center', 'halaqa'])
        );

        if ($matchingRoles->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        $branchIds = $matchingRoles->where('pivot.scope_type', 'branch')->pluck('pivot.scope_id');
        $regionIds = $matchingRoles->where('pivot.scope_type', 'region')->pluck('pivot.scope_id');
        $centerIds = $matchingRoles->where('pivot.scope_type', 'center')->pluck('pivot.scope_id');
        $halaqaIds = $matchingRoles->where('pivot.scope_type', 'halaqa')->pluck('pivot.scope_id');

        return $query->where(function (Builder $q) use ($branchIds, $regionIds, $centerIds, $halaqaIds) {

            // halaqa مباشرة
            if ($halaqaIds->isNotEmpty()) {
                $q->orWhereIn('id', $halaqaIds);
            }

            // تابعة لـ Center مباشرة
            if ($centerIds->isNotEmpty()) {
                $q->orWhere(function ($q) use ($centerIds) {
                    $q->where('reference_type', 'center')
                        ->whereIn('reference_id', $centerIds);
                });
            }

            // تابعة لـ Region مباشرة أو عبر Center
            if ($regionIds->isNotEmpty()) {
                $q->orWhere(function ($q) use ($regionIds) {
                    $q->where('reference_type', 'region')
                        ->whereIn('reference_id', $regionIds);
                });

                $centerIdsFromRegion = \App\Models\Center::whereIn('region_id', $regionIds)->pluck('id');
                if ($centerIdsFromRegion->isNotEmpty()) {
                    $q->orWhere(function ($q) use ($centerIdsFromRegion) {
                        $q->where('reference_type', 'center')
                            ->whereIn('reference_id', $centerIdsFromRegion);
                    });
                }
            }

            // تابعة لـ Branch عبر Region أو Center
            if ($branchIds->isNotEmpty()) {
                $regionIdsFromBranch = \App\Models\Region::whereIn('branch_id', $branchIds)->pluck('id');

                if ($regionIdsFromBranch->isNotEmpty()) {
                    $q->orWhere(function ($q) use ($regionIdsFromBranch) {
                        $q->where('reference_type', 'region')
                            ->whereIn('reference_id', $regionIdsFromBranch);
                    });

                    $centerIdsFromBranch = \App\Models\Center::whereIn('region_id', $regionIdsFromBranch)->pluck('id');
                    if ($centerIdsFromBranch->isNotEmpty()) {
                        $q->orWhere(function ($q) use ($centerIdsFromBranch) {
                            $q->where('reference_type', 'center')
                                ->whereIn('reference_id', $centerIdsFromBranch);
                        });
                    }
                }
            }
        });
    }

    // public function scopeVisibleTo(Builder $query, User $user): Builder
    // {
    //     $user->loadMissing('roles');

    //     $hasGlobalRole = $user->roles->contains(fn($role) => $role->pivot->scope_id === null);
    //     if ($hasGlobalRole) {
    //         return $query;
    //     }

    //     $matchingRoles = $user->roles->filter(
    //         fn($role) => in_array($role->pivot->scope_type, ['branch', 'region', 'center', 'halaqa'])
    //     );

    //     if ($matchingRoles->isEmpty()) {
    //         return $query->whereRaw('1 = 0');
    //     }

    //     return $query->where(function (Builder $q) use ($matchingRoles) {
    //         foreach ($matchingRoles as $role) {
    //             $scopeType = $role->pivot->scope_type;
    //             $scopeId   = $role->pivot->scope_id;

    //             match ($scopeType) {

    //                 // تابعة لـ Center مباشرة
    //                 'center' => $q->orWhere(function ($q) use ($scopeId) {
    //                     $q->where('reference_type', 'center')
    //                         ->where('reference_id', $scopeId);
    //                 }),

    //                 'region' => $q->orWhere(function ($q) use ($scopeId) {
    //                     // تابعة لـ Region مباشرة
    //                     $q->where(function ($q) use ($scopeId) {
    //                         $q->where('reference_type', 'region')
    //                             ->where('reference_id', $scopeId);
    //                     })
    //                         // تابعة لـ Center تابع لنفس الـ Region
    //                         ->orWhere(function ($q) use ($scopeId) {
    //                             $q->where('reference_type', 'center')
    //                                 ->whereIn(
    //                                     'reference_id',
    //                                     \App\Models\Center::where('region_id', $scopeId)->select('id')
    //                                 );
    //                         });
    //                 }),

    //                 'branch' => $q->orWhere(function ($q) use ($scopeId) {
    //                     // تابعة لـ Region تابع للفرع
    //                     $q->where(function ($q) use ($scopeId) {
    //                         $q->where('reference_type', 'region')
    //                             ->whereIn(
    //                                 'reference_id',
    //                                 \App\Models\Region::where('branch_id', $scopeId)->select('id')
    //                             );
    //                     })
    //                         // تابعة لـ Center تابع لـ Region تابع للفرع
    //                         ->orWhere(function ($q) use ($scopeId) {
    //                             $q->where('reference_type', 'center')
    //                                 ->whereIn(
    //                                     'reference_id',
    //                                     \App\Models\Center::whereIn(
    //                                         'region_id',
    //                                         \App\Models\Region::where('branch_id', $scopeId)->select('id')
    //                                     )->select('id')
    //                                 );
    //                         });
    //                 }),
    //                 'halaqa' => $q->orWhere('id', $scopeId),

    //                 default => null,
    //             };
    //         }
    //     });
    // }
}
