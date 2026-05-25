<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LevelTrackSubject extends Model
{
    protected $guarded = ['id'];
    /* protected $fillable = [
        'level_track_id',
        'subject_id',
        'is_required',
        'order',
    ]; */

    /* protected $casts = [
        'is_required' => 'boolean',
        'order' => 'integer',
    ]; */

    // ========================
    // Relations
    // ========================

    public function levelTrack(): BelongsTo
    {
        return $this->belongsTo(LevelTrack::class);
    }

    public function subject(): BelongsTo
    {
        return $this->belongsTo(Subject::class);
    }
}
