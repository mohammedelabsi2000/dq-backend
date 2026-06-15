<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Subject extends Model
{
    use SoftDeletes, HasFactory;

    protected $guarded = ['id'];

    // ========================
    // Relations
    // ========================

    public function track(): BelongsTo
    {
        return $this->belongsTo(Track::class)->withDefault();
    }
    
    public function subjectType(): BelongsTo
    {
        return $this->belongsTo(Constant::class);
    }

    public function levelTrackSubjects(): HasMany
    {
        return $this->hasMany(LevelTrackSubject::class);
    }

    public function levelTracks(): BelongsToMany
    {
        return $this->belongsToMany(LevelTrack::class, 'level_track_subjects')
            ->withPivot('is_required')
            ->withTimestamps();
    }
}
