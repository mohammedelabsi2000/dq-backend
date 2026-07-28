<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentLevelHistory extends Model
{
    use SoftDeletes;

    public static $usesAudit = true;

    protected $fillable = [
        'student_plan_id',
        'level_id',
        'from_date',
        'to_date',
        'notes',
        'created_by',
        'updated_by',
        'deleted_by',
    ];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
    ];

    // ========================
    // Relations
    // ========================

    public function studentPlan(): BelongsTo
    {
        return $this->belongsTo(StudentPlan::class);
    }

    public function level(): BelongsTo
    {
        return $this->belongsTo(Level::class);
    }

    // ========================
    // Scopes
    // ========================

    public function scopeCurrent($query)
    {
        return $query->whereNull('to_date');
    }
}