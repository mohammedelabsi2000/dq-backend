<?php

namespace App\Models;

use App\Concerns\HasApproval;
use App\Concerns\HasVisibilityScope;
use App\Enums\ApprovalLevel;
use App\Enums\Gender;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, HasVisibilityScope, HasApproval;

    protected $guard_name = 'sanctum';

    // مميز الموديلات اللي تستخدم audit يتم قرائته داخل AppServiceProvider.php
    public static $usesAudit = true;


    protected $fillable = [
        // Auth
        'name',
        'email',
        'password',
        // Personal info
        'fName',
        'sName',
        'thName',
        'family',
        'identity',
        'dob',
        'gender',
        'prefix_name_id',
        // Contact
        'phone',
        'whatsapp',
        // Location
        'mosque_id',
        'location',
        // Status
        'marital_status_id',
        'numChildren',
        // Job
        'jobname',
        'job_place',
        'job_salary',
        // Media
        'image',
        'is_approved',
        'is_active',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'dob' => 'date',
        'job_salary' => 'decimal:2',
        'gender' => Gender::class,
        'is_approved' => 'boolean',
        'is_active' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */


    // =======================
    // العلاقات (Relationships)
    // =======================

    // علاقة المستخدم بالمسجد


    public function attendances()
    {
        return $this->morphMany(Attendance::class, 'attendable');
    }

    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    // علاقة المستخدم بالحالة الاجتماعية
    public function maritalStatus()
    {
        return $this->belongsTo(Constant::class, 'marital_status_id');
    }

    // public function prefixName()
    // // علاقة المستخدم بالبادئة (prefix)
    // }

    public function prefix()
    {
        return $this->belongsTo(Constant::class, 'prefix_name_id');
    }

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function images()
    {
        return $this->morphMany(\App\Models\Image::class, 'imageable');
    }

    public function mainImage()
    {
        return $this->morphOne(\App\Models\Image::class, 'imageable')
            ->where('is_main', true);
    }

    protected static function booted()
    {
        static::deleting(function ($user) {

            foreach ($user->images as $image) {

                // حذف من التخزين
                Storage::disk($image->disk)->delete($image->file_path);

                // حذف من قاعدة البيانات
                $image->delete();
            }
        });
    }


    public function getPersonNameAttribute()
    {
        if ($this->person) {
            return "{$this->person->fName} {$this->person->sName} {$this->person->thName} {$this->person->family}";
        }
        return $this->person_type . ' #' . $this->person_id;
    }

    public function getGenderTextAttribute()
    {
        return $this->gender?->label() ?? 'غير محدد';
    }


    public function academicQualifications()
    {
        return $this->morphMany(AcademicQualification::class, 'person');
    }

    public function personalCourses()
    {
        return $this->morphMany(PersonalCourse::class, 'person');
    }

    public function activeScopes()
    {
        return $this->hasMany(UserScope::class)->whereNull('to_date');
    }

    // public function scopeVisibleTo(Builder $query, User $user): Builder
    // {
    //     if ($user->isGlobalAdmin()) {
    //         return $query;
    //     }

    //     $branchIds  = $user->getScopeIds('branch');
    //     $regionIds  = $user->getScopeIds('region');
    //     $centerIds  = $user->getScopeIds('center');
    //     $halaqaIds  = $user->getScopeIds('halaqa');

    //     // مدير حلقة فقط ← لا يرى أي مستخدم
    //     if ($halaqaIds->isNotEmpty() && $branchIds->isEmpty() && $regionIds->isEmpty() && $centerIds->isEmpty()) {
    //         return $query->whereRaw('1 = 0');
    //     }

    //     // توسيع من branch ← regions
    //     if ($branchIds->isNotEmpty() && $regionIds->isEmpty() && $centerIds->isEmpty()) {
    //         $regionIds = $regionIds->merge(
    //             Region::whereIn('branch_id', $branchIds)->pluck('id')
    //         )->unique();
    //     }

    //     // جمع mosque_ids عبر الهرمية
    //     $mosqueIds = collect();

    //     if ($regionIds->isNotEmpty()) {
    //         $mosqueIds = $mosqueIds->merge(
    //             Mosque::whereIn('region_id', $regionIds)->pluck('id')
    //         )->unique();
    //     }

    //     if ($centerIds->isNotEmpty()) {
    //         $mosqueIds = $mosqueIds->merge(
    //             Mosque::whereIn(
    //                 'id',
    //                 Center::whereIn('id', $centerIds)->pluck('mosque_id')
    //             )->pluck('id')
    //         )->unique();
    //     }

    //     // جمع user_ids عبر UserScope
    //     $scopedUserIds = collect();

    //     if ($branchIds->isNotEmpty()) {
    //         $scopedUserIds = $scopedUserIds->merge(
    //             UserScope::where('scope_type', 'branch')
    //                 ->whereIn('scope_id', $branchIds)
    //                 ->pluck('user_id')
    //         );
    //     }

    //     if ($regionIds->isNotEmpty()) {
    //         $scopedUserIds = $scopedUserIds->merge(
    //             UserScope::where('scope_type', 'region')
    //                 ->whereIn('scope_id', $regionIds)
    //                 ->pluck('user_id')
    //         );
    //     }

    //     if ($centerIds->isNotEmpty()) {
    //         $scopedUserIds = $scopedUserIds->merge(
    //             UserScope::where('scope_type', 'center')
    //                 ->whereIn('scope_id', $centerIds)
    //                 ->pluck('user_id')
    //         );
    //     }

    //     $scopedUserIds = $scopedUserIds->unique();

    //     // إذا ما في شيء على الإطلاق
    //     if ($mosqueIds->isEmpty() && $scopedUserIds->isEmpty()) {
    //         return $query->whereRaw('1 = 0');
    //     }

    //     return $query->where(function (Builder $q) use ($mosqueIds, $scopedUserIds) {
    //         if ($mosqueIds->isNotEmpty()) {
    //             $q->orWhereIn('mosque_id', $mosqueIds);
    //         }
    //         if ($scopedUserIds->isNotEmpty()) {
    //             $q->orWhereIn('id', $scopedUserIds);
    //         }
    //     });
    // }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isGlobalAdmin()) {
            return $query;
        }

        $branchIds = $user->getScopeIds('branch');
        $regionIds = $user->getScopeIds('region');
        $centerIds = $user->getScopeIds('center');
        $halaqaIds = $user->getScopeIds('halaqa');

        // معلم — يرى فقط المستخدمين في حلقاته
        if ($halaqaIds->isNotEmpty()) {
            $scopedUserIds = UserScope::where('scope_type', 'halaqa')
                ->whereIn('scope_id', $halaqaIds)
                ->whereNull('to_date')
                ->pluck('user_id');

            if ($scopedUserIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('id', $scopedUserIds);
        }

        // مدير مركز — يرى المستخدمين المعينين لمركزه والحلقات التابعة له
        if ($centerIds->isNotEmpty()) {
            $halaqaIdsInCenter = Halaqa::where('reference_type', 'center')
                ->whereIn('reference_id', $centerIds)
                ->pluck('id');

            $scopedUserIds = UserScope::whereNull('to_date')
                ->where(function ($q) use ($centerIds, $halaqaIdsInCenter) {
                    $q->where(function ($q) use ($centerIds) {
                        $q->where('scope_type', 'center')
                            ->whereIn('scope_id', $centerIds);
                    })->orWhere(function ($q) use ($halaqaIdsInCenter) {
                        $q->where('scope_type', 'halaqa')
                            ->whereIn('scope_id', $halaqaIdsInCenter);
                    });
                })
                ->pluck('user_id');

            if ($scopedUserIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('id', $scopedUserIds);
        }

        // مدير منطقة — يرى المستخدمين المعينين للمنطقة والمراكز والحلقات التابعة لها
        if ($regionIds->isNotEmpty()) {
            $centerIdsInRegion = Center::whereIn('region_id', $regionIds)->pluck('id');
            $halaqaIdsInRegion = Halaqa::where('reference_type', 'region')
                ->whereIn('reference_id', $regionIds)
                ->pluck('id');
            $halaqaIdsInCenters = Halaqa::where('reference_type', 'center')
                ->whereIn('reference_id', $centerIdsInRegion)
                ->pluck('id');
            $allHalaqaIds = $halaqaIdsInRegion->merge($halaqaIdsInCenters);

            $scopedUserIds = UserScope::whereNull('to_date')
                ->where(function ($q) use ($regionIds, $centerIdsInRegion, $allHalaqaIds) {
                    $q->where(function ($q) use ($regionIds) {
                        $q->where('scope_type', 'region')
                            ->whereIn('scope_id', $regionIds);
                    })->orWhere(function ($q) use ($centerIdsInRegion) {
                        $q->where('scope_type', 'center')
                            ->whereIn('scope_id', $centerIdsInRegion);
                    })->orWhere(function ($q) use ($allHalaqaIds) {
                        $q->where('scope_type', 'halaqa')
                            ->whereIn('scope_id', $allHalaqaIds);
                    });
                })
                ->pluck('user_id');

            if ($scopedUserIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('id', $scopedUserIds);
        }

        // مدير فرع — يرى المستخدمين المعينين للفرع والمناطق والمراكز والحلقات التابعة له
        if ($branchIds->isNotEmpty()) {
            $regionIdsInBranch = Region::whereIn('branch_id', $branchIds)->pluck('id');
            $centerIdsInBranch = Center::whereIn('region_id', $regionIdsInBranch)->pluck('id');

            // Get halaqat in regions directly
            $halaqaIdsInRegions = Halaqa::where('reference_type', 'region')
                ->whereIn('reference_id', $regionIdsInBranch)
                ->pluck('id');
            // Get halaqat in centers
            $halaqaIdsInCenters = Halaqa::where('reference_type', 'center')
                ->whereIn('reference_id', $centerIdsInBranch)
                ->pluck('id');
            $allHalaqaIds = $halaqaIdsInRegions->merge($halaqaIdsInCenters);

            $scopedUserIds = UserScope::whereNull('to_date')
                ->where(function ($q) use ($branchIds, $regionIdsInBranch, $centerIdsInBranch, $allHalaqaIds) {
                    $q->where(function ($q) use ($branchIds) {
                        $q->where('scope_type', 'branch')
                            ->whereIn('scope_id', $branchIds);
                    })->orWhere(function ($q) use ($regionIdsInBranch) {
                        $q->where('scope_type', 'region')
                            ->whereIn('scope_id', $regionIdsInBranch);
                    })->orWhere(function ($q) use ($centerIdsInBranch) {
                        $q->where('scope_type', 'center')
                            ->whereIn('scope_id', $centerIdsInBranch);
                    })->orWhere(function ($q) use ($allHalaqaIds) {
                        $q->where('scope_type', 'halaqa')
                            ->whereIn('scope_id', $allHalaqaIds);
                    });
                })
                ->pluck('user_id');

            if ($scopedUserIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('id', $scopedUserIds);
        }

        return $query->whereRaw('1 = 0');
    }


    public function approvalLevel(): ?ApprovalLevel
    {
        if ($this->isGlobalAdmin())                      return ApprovalLevel::Admin;
        if ($this->getScopeIds('branch')->isNotEmpty())  return ApprovalLevel::Branch;
        if ($this->getScopeIds('region')->isNotEmpty())  return ApprovalLevel::Region;
        if ($this->getScopeIds('center')->isNotEmpty())  return null; // مدير المركز — يبدأ من Region
        return null;
    }
}
