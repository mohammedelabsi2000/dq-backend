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

    public function levelTrackSubjects(): HasMany
    {
        return $this->hasMany(LevelTrackSubject::class);
    }

    public function subjects(): BelongsToMany
    {
        return $this->belongsToMany(Subject::class, 'level_track_subjects')
            ->withPivot('is_required')
            ->withTimestamps();
    }
}
