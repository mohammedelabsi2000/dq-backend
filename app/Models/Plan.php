<?php

namespace App\Models;

use App\Enums\PeriodUnit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes, HasFactory;

    protected $guarded = ['id'];

    /* protected $fillable = [
        'name',
        'description',
        'period_unit',
        'period',
        'min_period',
        'max_period',
        'tolerance',
        'is_active',
    ]; */
    protected $casts = [
        'period_unit' => PeriodUnit::class,
    ];

    /* protected $casts = [
        'period_unit' => 'string',
        'period' => 'integer',
        'min_period' => 'integer',
        'max_period' => 'integer',
        'tolerance' => 'integer',
        'is_active' => 'boolean',
    ]; */

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

    public function scopeIsActive(Builder $query)
    {
        return $query->where('is_active', true);
    }
}
