<?php

namespace App\Models;

use App\Enums\StudentPlanStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class StudentPlan extends Model
{
    protected $fillable = [
        'student_id',
        'plan_id',
        'starting_level_id',
        'current_level_id',
        'from_date',
        'to_date',
        'is_main',
        'status',
        'notes',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'is_main' => 'boolean',
        'status' => StudentPlanStatus::class,
    ];

    // ========================
    // Relations
    // ========================

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function startingLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'starting_level_id');
    }

    public function currentLevel(): BelongsTo
    {
        return $this->belongsTo(Level::class, 'current_level_id');
    }

    public function levelHistory(): HasMany
    {
        return $this->hasMany(StudentLevelHistory::class)->orderBy('from_date');
    }

    // ========================
    // Scopes
    // ========================

    public function scopeActive($query)
    {
        return $query->whereNull('to_date');
    }

    public function scopeMain($query)
    {
        return $query->where('is_main', true);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    // ========================
    // Business Logic
    // ========================

    /**
     * تعيين هذه الخطة كرئيسية - يلغي الرئيسية عن أي خطة نشطة أخرى لنفس الطالب
     */
    public function setAsMain(): void
    {
        static::where('student_id', $this->student_id)
              ->where('id', '!=', $this->id)
              ->active()
              ->update(['is_main' => false]);

        $this->update(['is_main' => true]);
    }

    /**
     * نقل الطالب لمستوى جديد - يغلق سجل المستوى الحالي في history ويفتح سجلاً جديداً
     */
    public function moveToLevel(int $newLevelId, ?string $date = null, ?string $notes = null): void
    {
        $date = $date ?? now()->toDateString();

        $this->levelHistory()
             ->whereNull('to_date')
             ->update(['to_date' => $date]);

        $this->levelHistory()->create([
            'level_id'   => $newLevelId,
            'from_date'  => $date,
            'to_date'    => null,
            'notes'      => $notes,
        ]);

        $this->update(['current_level_id' => $newLevelId]);
    }

    /**
     * إغلاق الخطة الحالية للطالب (إكمال / انتقال / انقطاع)
     * لو كانت هي الخطة الرئيسية، تُنقل الرئيسية تلقائياً لأقدم خطة نشطة أخرى (إن وجدت)
     */
    public function closePlan(StudentPlanStatus $status, ?string $date = null, ?string $notes = null): void
    {
        $date = $date ?? now()->toDateString();

        $this->levelHistory()
             ->whereNull('to_date')
             ->update(['to_date' => $date]);

        $wasMain = $this->is_main;

        $this->update([
            'to_date' => $date,
            'status'  => $status,
            'is_main' => false,
            'notes'   => $notes ?? $this->notes,
        ]);

        // لو كانت الخطة الرئيسية وأُغلقت، عيّن أقدم خطة نشطة أخرى كرئيسية تلقائياً
        if ($wasMain) {
            $nextPlan = static::where('student_id', $this->student_id)
                ->active()
                ->orderBy('from_date')
                ->first();

            $nextPlan?->update(['is_main' => true]);
        }
    }
}