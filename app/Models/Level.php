<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Level extends Model
{
    use SoftDeletes, HasFactory;
    protected $guarded = ['id'];

    /* protected $fillable = [
        'plan_id',
        'name',
        'order',
        'duration',
        'max_duration',
        'min_duration',
        'duration_unit',
        'notes',
    ]; */

    /* protected $casts = [
        'order'         => 'integer',
        'duration'      => 'integer',
        'max_duration'  => 'integer',
        'min_duration'  => 'integer',
        'duration_unit' => 'string',
    ]; */

    // ========================
    // Relations
    // ========================

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function levelTracks(): HasMany
    {
        return $this->hasMany(LevelTrack::class);
    }

    public function tracks(): BelongsToMany
    {
        return $this->belongsToMany(Track::class, 'level_tracks')
            ->withPivot('weight', 'id')
            ->withTimestamps();
    }
}
