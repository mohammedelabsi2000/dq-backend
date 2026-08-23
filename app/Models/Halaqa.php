<?php

namespace App\Models;

use App\Concerns\HasApproval;
use App\Concerns\HasVisibilityScope;
use App\Enums\HalaqaReferenceType;
use App\Models\Scopes\GenderVisibilityScope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Halaqa extends Model
{
    use HasFactory, SoftDeletes, HasVisibilityScope, HasApproval;

    public static $usesAudit = true;

    protected $table = 'halaqas';

    /* protected $fillable = [
        'name',
        'location',
        'description',
        'reference_type',
        'reference_id',
        'type_id',
    ]; */

    protected $guarded = ['center_id', 'region_id'];

    protected $casts = [
        'reference_type' => HalaqaReferenceType::class,
        'is_approved' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected $with = ['lastStatus'];
    protected static function booted()
    {
        static::addGlobalScope(new GenderVisibilityScope);
    }
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
            ->withPivot(['id', 'from_date', 'to_date', 'enrollment_status_id'])
            ->wherePivotNull('deleted_at')
            ->withTimestamps();
        // ->using(HalaqaStudent::class);
    }

    /**
     * Get the supervisor (mobile) of this halaqa based on scoped role assignments.
     */
    public function supervisors()
    {
        return $this->hasMany(UserScope::class, 'scope_id')
            ->where('scope_type', 'halaqa')
            ->active()
            ->with('user');
    }

    public function lastSupervisor()
    {
        return $this->hasOne(UserScope::class, 'scope_id')
            ->where('scope_type', 'halaqa')
            ->latestOfMany('id')
            ->with('user');
    }

    public function statuses()
    {
        return $this->hasMany(HalaqaStatus::class, 'halaqa_id');
    }

    public function lastStatus()
    {
        logger('Last status relation');
        return $this->hasOne(HalaqaStatus::class, 'halaqa_id')->latestOfMany('id')->with(['sponsorshipType']);
    }

    public function sponsorships()
    {
        return $this->hasMany(HalaqaSponsorship::class);
    }

    public function activeSponsorships()
    {
        return $this->hasMany(HalaqaSponsorship::class)->whereNull('to_date');
    }

    public function sponsors()
    {
        return $this->belongsToMany(Sponsor::class, 'halaqa_sponsorships')
            ->withPivot(['id', 'from_date', 'to_date', 'notes'])
            ->wherePivotNull('to_date');
    }

    /**
     * Get the count of active students in this halaqa.
     */
    // public function studentsCount()
    // {
    //     return $this->studentEnrollments()
    //         ->whereNull('to_date')
    //         ->orWhere('to_date', '>=', now())
    //         ->count();
    // }
    public function studentsCount()
    {
        return $this->studentEnrollments()
            ->whereHas('student')  // ← يستثني المحذوفين تلقائياً
            ->where(function ($q) {
                $q->whereNull('to_date')
                    ->orWhere('to_date', '>=', now());
            })
            ->count();
    }

    public function autoApprovalEnabled(): bool
    {
        return Setting::isEnabled(Setting::AUTO_APPROVE_HALAQAS);
    }

    public function scopeIsActive(Builder $query)
    {
        return $query->where('is_active', true);
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

        // محفظ حلقة ← حلقته فقط
        if ($halaqaIds->isNotEmpty()) {
            return $query->whereIn('id', $halaqaIds);
        }

        // مدير مركز ← حلقات مركزه فقط
        if ($centerIds->isNotEmpty()) {
            return $query->where('reference_type', 'center')
                ->whereIn('reference_id', $centerIds);
        }

        // مدير منطقة ← حلقات منطقته (مباشرة + عبر مراكزها)
        if ($regionIds->isNotEmpty()) {
            $centerIds = Center::whereIn('region_id', $regionIds)->pluck('id');

            return $query->where(function (Builder $q) use ($regionIds, $centerIds) {
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
            });
        }

        // مدير فرع ← حلقات كل مناطق فرعه (مباشرة + عبر مراكزها)
        if ($branchIds->isNotEmpty()) {
            $regionIds = Region::whereIn('branch_id', $branchIds)->pluck('id');
            $centerIds = Center::whereIn('region_id', $regionIds)->pluck('id');

            if ($regionIds->isEmpty() && $centerIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            return $query->where(function (Builder $q) use ($regionIds, $centerIds) {
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
            });
        }

        return $query->whereRaw('1 = 0');
    }
}
