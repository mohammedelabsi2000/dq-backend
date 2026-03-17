<?php

namespace App\Models;

use App\Concerns\HasHierarchyScope;
use App\Concerns\HasRoles;
use App\Contracts\BelongsToHierarchy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements BelongsToHierarchy
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes, HasRoles, HasHierarchyScope;

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


    public function academicQualifications()
    {
        return $this->morphMany(AcademicQualification::class, 'person');
    }
    public function personalCourses()
    {
        return $this->morphMany(PersonalCourse::class, 'person');
    }
    public function getHierarchyIds(): array
    {
        return [
            ['id' => $this->id, 'type' => 'user'],
        ];
    }

    // public function getHierarchyIds(): array
    // {
    //     $this->loadMissing('mosque.region');

    //     $mosque = $this->mosque;
    //     if (!$mosque) return [['id' => $this->id, 'type' => 'user']];

    //     $region = $mosque->region;
    //     if (!$region) return [
    //         ['id' => $mosque->id,  'type' => 'mosque'],
    //         ['id' => $this->id,    'type' => 'user'],
    //     ];

    //     return [
    //         ['id' => $region->branch_id, 'type' => 'branch'],
    //         ['id' => $region->id,        'type' => 'region'],
    //         ['id' => $mosque->id,        'type' => 'mosque'],
    //         ['id' => $this->id,          'type' => 'user'],
    //     ];
    // }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $this->applyVisibleTo($query, $user, [
            'branch' => fn(Builder $q, $id) =>
            $q->orWhereHas('mosque.region', fn($r) => $r->where('branch_id', $id)),
            'region' => fn(Builder $q, $id) =>
            $q->orWhereHas('mosque', fn($m) => $m->where('region_id', $id)),
            'mosque' => 'mosque_id',
            'user'   => 'id',
        ]);
    }

    public function scopeOnlyTeachers(Builder $query)
    {
        return $query->whereHas('roles', function ($q) {
            $q->where('name', 'teacher');
        });
    }
}
