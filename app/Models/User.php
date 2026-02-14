<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    protected $appends = ['full_name'];

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



    public function getPersonNameAttribute()
{
    if ($this->person) {
        return "{$this->person->fName} {$this->person->sName} {$this->person->thName} {$this->person->family}";
    }
    return $this->person_type . ' #' . $this->person_id;
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
}
