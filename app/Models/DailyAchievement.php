<?php

namespace App\Models;

use App\Enums\AchievementStatus;
use App\Enums\AchievementType;
use App\Enums\EvaluationGrade;
use App\Models\Quran\Surah;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DailyAchievement extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'student_id',
        'teacher_id',
        'subject_id',
        'date',
        'from_surah',
        'from_ayah',
        'to_surah',
        'to_ayah',
        'ayahs_count',
        'pages_count',
        'achievement_type',
        'evaluation_grade',
        'achievement_status',
        'mistakes_count',
        'notes',
        'recorded_at',
    ];

    protected $casts = [
        'date' => 'date',
        'from_surah' => 'integer',
        'from_ayah' => 'integer',
        'to_surah' => 'integer',
        'to_ayah' => 'integer',
        'ayahs_count' => 'integer',
        'pages_count' => 'decimal:2',
        'mistakes_count' => 'integer',
        'recorded_at' => 'datetime',
        'achievement_type' => AchievementType::class,
        'evaluation_grade' => EvaluationGrade::class,
        'achievement_status' => AchievementStatus::class,
    ];

    // ========================
    // Relations
    // ========================

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function fromSurah()
    {
        return $this->belongsTo(Surah::class, 'from_surah');
    }

    public function toSurah()
    {
        return $this->belongsTo(Surah::class, 'to_surah');
    }

    // ========================
    // Scopes
    // ========================

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('date', $date);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeByType($query, AchievementType $type)
    {
        return $query->where('achievement_type', $type);
    }

    public function scopeByStatus($query, AchievementStatus $status)
    {
        return $query->where('achievement_status', $status);
    }
}