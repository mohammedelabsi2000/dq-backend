<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanTrackCourse extends Model
{
    protected $fillable = [
        'plan_track_id',
        'course_id',
        'order',
        'is_required'
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function planTrack()
    {
        return $this->belongsTo(PlanTrack::class);
    }
}
