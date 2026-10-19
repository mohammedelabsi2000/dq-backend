<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * محاولة اختبار نهائي لطالب في مادة ضمن لجنة. النتيجة تصل من برنامج الجودة.
 */
class FinalExam extends Model
{
    use HasFactory, SoftDeletes;

    public static $usesAudit = true;

    public const PASSED = 'ناجح';
    public const FAILED = 'راسب';
    public const ABSENT = 'غائب';

    public const RESULTS = [self::PASSED, self::FAILED, self::ABSENT];

    protected $guarded = [];

    protected $casts = [
        'mark' => 'float',
        'success_mark' => 'float',
        'attempt_number' => 'integer',
        'exam_date' => 'date',
    ];

    public function studentSubject()
    {
        return $this->belongsTo(StudentSubject::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function committee()
    {
        return $this->belongsTo(ExamCommittee::class, 'exam_committee_id');
    }

    public function isPassed(): bool
    {
        return $this->result === self::PASSED;
    }
}
