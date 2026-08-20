<?php

namespace App\Models;

use App\Enums\PeriodUnit;
use App\Enums\PlanType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Plan extends Model
{
    use SoftDeletes, HasFactory;

    public static $usesAudit = true;

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
        'type',
        'age_from',
        'age_to',
    ]; */
    protected $casts = [
        'period_unit' => PeriodUnit::class,
        'type' => PlanType::class,
    ];

    /* protected $casts = [
        'period_unit' => 'string',
        'period' => 'integer',
        'min_period' => 'integer',
        'max_period' => 'integer',
        'tolerance' => 'integer',
        'is_active' => 'boolean',
    ]; */

    protected static function booted()
    {
        // تم إزالة التقييد الذي يمنع وجود أكثر من خطة رئيسية نشطة
        // static::saved(function (Plan $plan) {
        //     if ($plan->type === PlanType::Main && $plan->is_active) {
        //         static::where('type', PlanType::Main->value)
        //             ->where('id', '!=', $plan->id)
        //             ->where('is_active', true)
        //             ->update(['is_active' => false]);
        //     }
        // });
    }

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

    /**
     * هل هذه الخطة هي الخطة الرئيسية الفعالة الوحيدة حالياً؟
     */
    public function isActiveMain(): bool
    {
        return $this->type === PlanType::Main && $this->is_active;
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'student_plans')
        ->withPivot(['is_main', 'status', 'starting_level_id', 'current_level_id', 'from_date', 'to_date', 'notes']);
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
