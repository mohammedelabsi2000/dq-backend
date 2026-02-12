<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
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

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'dob' => 'date',
        'job_salary' => 'decimal:2',
    ];

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

    // علاقة المستخدم بالبادئة (prefix)
    public function prefix()
    {
        return $this->belongsTo(Constant::class, 'prefix_name_id');
    }

    // لو عندك جدول للصور وتضيف image_id لاحقًا
    /*
    public function image()
    {
        return $this->belongsTo(Image::class);
    }
    */
}
