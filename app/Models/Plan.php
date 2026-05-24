<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'duration',
        'duration_unit',
        'is_active',
        'tolerance',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'duration'   => 'integer',
        'tolerance'  => 'integer',
        'duration_unit' => 'string',
    ];

    // ========================
    // Relations
    // ========================

    public function levels(): HasMany
    {
        return $this->hasMany(Level::class)->orderBy('order');
    }

    // ========================
    // Scopes
    // ========================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
