<?php

namespace App\Models;

use App\Concerns\HasVisibilityScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Halaqa extends Model
{
    use HasFactory, SoftDeletes, HasVisibilityScope;

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
            ->withPivot(['from_date', 'to_date', 'enrollment_status_id'])
            ->withTimestamps();
        // ->using(HalaqaStudent::class);
    }

    /**
     * Get the supervisor (محفظ) of this halaqa based on role_user permissions.
     */
    public function supervisor()
    {
        return $this->belongsToMany(User::class, 'role_user', 'scope_id', 'authorizable_id')
            ->where('scope_type', 'halaqas');
    }

    /**
     * Get the count of active students in this halaqa.
     */
    public function studentsCount()
    {
        return $this->studentEnrollments()
            ->whereNull('to_date')
            ->orWhere('to_date', '>=', now())
            ->count();
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

        return $query->where(function (Builder $q) use ($regionIds, $centerIds, $halaqaIds) {
            if ($halaqaIds->isNotEmpty()) {
                $q->orWhereIn('id', $halaqaIds);
            }
            if ($regionIds->isNotEmpty()) {
                $q->orWhere(function ($q) use ($regionIds) {
                    $q->where('reference_type', 'region')
                        ->whereIn('reference_id', $regionIds);
                });
            }
            if ($centerIds->isNotEmpty()) {
                $q->orWhere(function ($q) use ($centerIds) {
                    $q->where('reference_type', 'center')
                        ->whereIn('reference_id', $centerIds);
                });
            }
            if ($halaqaIds->isEmpty() && $regionIds->isEmpty() && $centerIds->isEmpty()) {
                $q->whereRaw('1 = 0');
            }
        });
    }
}
