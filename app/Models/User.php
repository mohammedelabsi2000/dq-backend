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

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isGlobalAdmin()) {
            return $query;
        }

        $branchIds = $user->getScopeIds('branch');
        $regionIds = $user->getScopeIds('region');
        $centerIds = $user->getScopeIds('center');
        $halaqaIds = $user->getScopeIds('halaqa');

        // مدير مركز — يرى فقط من scope_type = center في مراكزه
        if ($centerIds->isNotEmpty()) {
            $scopedUserIds = UserScope::where('scope_type', 'center')
                ->whereIn('scope_id', $centerIds)
                ->whereNull('to_date')
                ->pluck('user_id');

            if ($scopedUserIds->isEmpty()) {
                return $query->whereRaw('1 = 0');
            }

            return $query->whereIn('id', $scopedUserIds);
        }

        // مدير منطقة — يرى من scope_type = region + center في منطقته
        if ($regionIds->isNotEmpty()) {
            $centerIdsInRegion = Center::whereIn('region_id', $regionIds)->pluck('id');

            $scopedUserIds = UserScope::whereNull('to_date')
                ->where(function ($q) use ($regionIds, $centerIdsInRegion) {
                    $q->where(function ($q) use ($regionIds) {
                        $q->where('scope_type', 'region')
                            ->whereIn('scope_id', $regionIds);
                    })->orWhere(function ($q) use ($centerIdsInRegion) {
                        $q->where('scope_type', 'center')
                            ->whereIn('scope_id', $centerIdsInRegion);
                    });
                })
                ->pluck('user_id');

            $mosqueIds = Mosque::whereIn('region_id', $regionIds)->pluck('id');

            return $this->buildUserVisibilityQuery($query, $mosqueIds, $scopedUserIds);
        }

        // مدير فرع — يرى الكل في فرعه
        if ($branchIds->isNotEmpty()) {
            $regionIdsInBranch = Region::whereIn('branch_id', $branchIds)->pluck('id');
            $centerIdsInBranch = Center::whereIn('region_id', $regionIdsInBranch)->pluck('id');
            $mosqueIds = Mosque::whereIn('region_id', $regionIdsInBranch)->pluck('id');

            $scopedUserIds = UserScope::whereNull('to_date')
                ->where(function ($q) use ($branchIds, $regionIdsInBranch, $centerIdsInBranch) {
                    $q->where(function ($q) use ($branchIds) {
                        $q->where('scope_type', 'branch')
                            ->whereIn('scope_id', $branchIds);
                    })->orWhere(function ($q) use ($regionIdsInBranch) {
                        $q->where('scope_type', 'region')
                            ->whereIn('scope_id', $regionIdsInBranch);
                    })->orWhere(function ($q) use ($centerIdsInBranch) {
                        $q->where('scope_type', 'center')
                            ->whereIn('scope_id', $centerIdsInBranch);
                    });
                })
                ->pluck('user_id');

            return $this->buildUserVisibilityQuery($query, $mosqueIds, $scopedUserIds);
        }

        return $query->whereRaw('1 = 0');
    }

    private function buildUserVisibilityQuery(Builder $query, $mosqueIds, $scopedUserIds): Builder
    {
        if ($mosqueIds->isEmpty() && $scopedUserIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        return $query->where(function (Builder $q) use ($mosqueIds, $scopedUserIds) {
            if ($mosqueIds->isNotEmpty()) {
                $q->orWhereIn('mosque_id', $mosqueIds);
            }
            if ($scopedUserIds->isNotEmpty()) {
                $q->orWhereIn('id', $scopedUserIds);
            }
        });
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
