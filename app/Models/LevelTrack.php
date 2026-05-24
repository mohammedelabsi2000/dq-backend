<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LevelTrack extends Model
{
    protected $fillable = [
        'level_id',
        'track_id',
        'weight',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
    ];

    // ========================
    // Relations
    // ========================

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class);
    }

    public function levelTrackCourses(): HasMany
    {
        return $this->hasMany(LevelTrackCourse::class);
    }

    public function courses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'level_track_courses')
            ->withPivot('is_required')
            ->withTimestamps();
    }
}
