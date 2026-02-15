<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'track_id',
        'name',
        'book_name',
        'description',
        'hours',
        'max_score'
    ];

    public function track()
    {
        return $this->belongsTo(Track::class);
    }

    public function planTracks()
    {
        return $this->belongsToMany(
            PlanTrack::class,
            'plan_track_courses'
        )->withPivot('order','is_required')
         ->withTimestamps();
    }
}
