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
        return $this->hasMany(Level::class, 'plan_id', 'id')->orderBy('order');
    }

    // ========================
    // Scopes
    // ========================

    public function scopeIsActive(Builder $query)
    {
        return $query->where('is_active', true);
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_plans')
        ->withPivot('is_main', 'status', 'starting_level_id', 'current_level_id', 'from_date', 'to_date', 'notes');
        // return $this->hasMany(StudentPlan::class, 'plan_id', 'id');
        // return $this->hasManyThrough(Student::class, StudentPlan::class, 'plan_id', 'id', 'id', 'student_id');
    }

    // current students
    public function currentStudents()
    {
        return $this->hasManyThrough(Student::class, StudentPlan::class, 'plan_id', 'id', 'id', 'student_id')
            ->where('status', 'active')
            ->whereDate('from_date', '<=', now())
            ->where(function ($query) {
                $query->whereDate('to_date', '>=', now())
                    ->orWhereNull('to_date');
            });
    }
}
