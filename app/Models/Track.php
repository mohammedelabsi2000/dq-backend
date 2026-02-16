<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Track extends Model
{
    protected $fillable = [
        'name',
        'description'
    ];

    // المسار يحتوي دورات
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    // علاقة many-to-many مع الخطط
    public function plans()
    {
        return $this->belongsToMany(
            Plan::class,
            'plan_tracks'
        )->withPivot('is_required','weight')
         ->withTimestamps();
    }
}
