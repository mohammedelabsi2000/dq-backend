<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plan extends Model
{
    protected $fillable = [
        'name',
        'weight',
        'duration_in_days',
        'grace_period_days',
        'is_active'
    ];

    // علاقة: الخطة تحتوي مسارات
    public function planTracks()
    {
        return $this->hasMany(PlanTrack::class);
    }
public function assignments()
{
    return $this->hasMany(PlanAssignment::class);
}

    // علاقة many-to-many مع المسارات
    public function tracks()
    {
        return $this->belongsToMany(
            Track::class,
            'plan_tracks'
        )->withPivot('is_required','weight')
         ->withTimestamps();
    }
}
