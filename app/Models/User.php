<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
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

    public function mosque()
    {
        return $this->belongsTo(Mosque::class);
    }

    public function maritalStatus()
    {
        return $this->belongsTo(Constant::class, 'marital_status_id');
    }

    public function prefixName()
    {
        return $this->belongsTo(Constant::class, 'prefix_name_id');
    }

    public function imageData()
    {
        return $this->belongsTo(Image::class, 'image');
    }
public function image()
{
    return $this->belongsTo(Image::class);
}

    /*
    |--------------------------------------------------------------------------
    | Accessors
    |--------------------------------------------------------------------------
    */

    public function getFullNameAttribute()
    {
        return "{$this->fName} {$this->sName} {$this->thName} {$this->family}";
    }
}
