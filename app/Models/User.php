<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

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
        'fName',
        'sName',
        'thName',
        'family',
        'dob',
        'mosque_id',
        'location',
        'gender',
        'marital_status_id',
        'numChildren',
        'identity',
        'phone',
        'whatsapp',
        'jobname',
        'job_place',
        'job_salary',
        'image',
        'prefix_name_id',
        'jobname',
        'job_place',
        'job_salary',
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

    /* public function getFullNameAttribute()
    {
        return trim(preg_replace('/\s+/', ' ', "{$this->fName} {$this->sName} {$this->thName} {$this->family}"));

        // if ($this->person) {
        //     return "{$this->person->fName} {$this->person->sName} {$this->person->thName} {$this->person->family}";
        // }
        // return $this->person_type . ' #' . $this->person_id;
    } */

    public function getGenderTextAttribute()
    {
        return $this->gender ?? 'غير محدد';
        /* return match ($this->gender) {
            'male' => 'ذكر',
            'female' => 'أنثى',
            default => 'غير محدد',
        }; */
    }


    // لو عندك جدول للصور وتضيف image_id لاحقًا
    /*
    public function image()
    {
        return $this->belongsTo(Image::class);
    }
    */

    public function academicQualifications()
    {
        return $this->morphMany(AcademicQualification::class, 'person');
    }
    public function personalCourses()
    {
        return $this->morphMany(PersonalCourse::class, 'person');
    }

    public function currentHalaqa()
    {
        return $this->hasOne(UserRole::class)
            ->where('relation_type', Halaqa::class)
            ->where('role', 'teacher')
            ->whereNull('end_date');

    }

    public function roles()
    {
        return $this->hasMany(UserRole::class);
    }

    // الأدوار النشطة فقط
    public function activeRoles()
    {
        return $this->roles()->active();
    }

    // أدوار ضمن كيان معين (مثلاً حلقة)
    public function rolesIn($model)
    {
        return $this->activeRoles()->forModel($model);
    }

    // هل عنده دور معين داخل كيان؟
    public function hasRoleIn(string $roleName, $model): bool
    {
        return $this->roles()
            ->active()
            ->forRole($roleName)
            ->forModel($model)
            ->exists();
    }
}
