<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanTrack extends Model
{
    protected $fillable = [
        'plan_id',
        'track_id',
        'is_required',
        'weight'
    ];

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function courses()
    {
        return $this->belongsToMany(
            Course::class,
            'plan_track_courses'
        )->withPivot('order','is_required')
         ->withTimestamps();
    }
}
