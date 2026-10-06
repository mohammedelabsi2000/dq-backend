<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LevelTrackSubject extends Model
{
    use SoftDeletes;

    public static $usesAudit = true;

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

    protected $casts = [
        // اتجاه خاص لكل جزء {custom_juz_id: direction}
        'juz_directions' => 'array',
    ];

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
