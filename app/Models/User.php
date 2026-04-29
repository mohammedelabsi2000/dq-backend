<?php

namespace App\Models;

use App\Concerns\HasHierarchyScope;
use App\Concerns\HasVisibilityScope;
use App\Contracts\BelongsToHierarchy;
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
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, HasVisibilityScope;

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


    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->isGlobalAdmin()) {
            return $query;
        }

        $branchIds  = $user->getScopeIds('branch');
        $regionIds  = $user->getScopeIds('region');
        $centerIds  = $user->getScopeIds('center');
        $halaqaIds  = $user->getScopeIds('halaqa');

        // مدير حلقة فقط ← لا يرى أي مستخدم
        if ($halaqaIds->isNotEmpty() && $branchIds->isEmpty() && $regionIds->isEmpty() && $centerIds->isEmpty()) {
            return $query->whereRaw('1 = 0');
        }

        // توسيع من branch ← regions
        if ($branchIds->isNotEmpty() && $regionIds->isEmpty() && $centerIds->isEmpty()) {
            $regionIds = $regionIds->merge(
                Region::whereIn('branch_id', $branchIds)->pluck('id')
            )->unique();
        }

        // جمع mosque_ids عبر الهرمية
        $mosqueIds = collect();

        if ($regionIds->isNotEmpty()) {
            $mosqueIds = $mosqueIds->merge(
                Mosque::whereIn('region_id', $regionIds)->pluck('id')
            )->unique();
        }

        if ($centerIds->isNotEmpty()) {
            $mosqueIds = $mosqueIds->merge(
                Mosque::whereIn(
                    'id',
                    Center::whereIn('id', $centerIds)->pluck('mosque_id')
                )->pluck('id')
            )->unique();
        }

        // جمع user_ids عبر UserScope
        $scopedUserIds = collect();

        if ($branchIds->isNotEmpty()) {
            $scopedUserIds = $scopedUserIds->merge(
                UserScope::where('scope_type', 'branch')
                    ->whereIn('scope_id', $branchIds)
                    ->pluck('user_id')
            );
        }

        if ($regionIds->isNotEmpty()) {
            $scopedUserIds = $scopedUserIds->merge(
                UserScope::where('scope_type', 'region')
                    ->whereIn('scope_id', $regionIds)
                    ->pluck('user_id')
            );
        }

        if ($centerIds->isNotEmpty()) {
            $scopedUserIds = $scopedUserIds->merge(
                UserScope::where('scope_type', 'center')
                    ->whereIn('scope_id', $centerIds)
                    ->pluck('user_id')
            );
        }

        $scopedUserIds = $scopedUserIds->unique();

        // إذا ما في شيء على الإطلاق
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

    /*
    |--------------------------------------------------------------------------
    | Scopes Management
    |--------------------------------------------------------------------------
    */

    public function scopes()
    {
        return $this->hasMany(UserScope::class);
    }

    public function syncScopes(array $scopes)
    {
        $this->scopes()->delete();

        foreach ($scopes as $scope) {
            $this->scopes()->create([
                'scope_type' => $scope['type'],
                'scope_id' => $scope['id'],
            ]);
        }
    }

    public function clearScopes()
    {
        $this->scopes()->delete();
    }
}
