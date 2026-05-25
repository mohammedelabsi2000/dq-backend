<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Track extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];
    /* protected $fillable = [
        'name',
        'notes',
    ]; */

    // ========================
    // Relations
    // ========================

    public function subjects(): HasMany
    {
        return $this->hasMany(Subject::class);
    }

    public function levels(): BelongsToMany
    {
        return $this->belongsToMany(Level::class, 'level_tracks')
            ->withPivot('weight', 'id')
            ->withTimestamps();
    }

    public function levelTracks(): HasMany
    {
        return $this->hasMany(LevelTrack::class);
    }
}
