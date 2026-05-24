<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LevelTrackCourse extends Model
{
    protected $fillable = [
        'level_track_id',
        'course_id',
        'is_required',
        'order',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer',
    ];

    // ========================
    // Relations
    // ========================

    public function levelTrack(): BelongsTo
    {
        return $this->belongsTo(LevelTrack::class);
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }
}
