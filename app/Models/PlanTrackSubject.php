<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanTrackSubject extends Model
{
    protected $fillable = [
        'plan_track_id',
        'subject_id',
        'order',
        'is_required'
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function planTrack()
    {
        return $this->belongsTo(PlanTrack::class);
    }
}
