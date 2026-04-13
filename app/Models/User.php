<?php

namespace App\Models;

use App\Concerns\HasVisibilityScope;
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

    // protected $appends = ['full_name'];

    protected $fillable = [
        'fName',
        'sName',
        'thName',
        'family',
        'name',
        'dob',
        'mosque_id',
        'location',
        'gender',
        'marital_status_id',
        'numChildren',
        'identity',
        'phone',
        'whatsapp',
        'email',
        'password',
        'jobname',
        'job_place',
        'job_salary',
        'image',
        'prefix_name_id',
        // 'image_id', // لو حبيت تضيفها لاحقًا
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

    //     public function imageData()
    //     {
    //         return $this->belongsTo(Image::class, 'image');
    //     }
    // public function image()
    // {
    //     return $this->belongsTo(Image::class);
    // }

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
        return $this->gender ?? 'غير محدد';
    }


    public function academicQualifications()
    {
        return $this->morphMany(AcademicQualification::class, 'person');
    }
    public function personalCourses()
    {
        return $this->morphMany(PersonalCourse::class, 'person');
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

    public function getScopesAttribute()
    {
        return $this->scopes()->get()->map(function ($scope) {
            return [
                'type' => $scope->scope_type,
                'id' => $scope->scope_id,
            ];
        });
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
