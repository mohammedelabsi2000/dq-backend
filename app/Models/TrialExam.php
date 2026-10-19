<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrialExam extends Model
{
    use HasFactory, SoftDeletes;

    public const PENDING = 'pending';
    public const PASSED = 'passed';
    public const FAILED = 'failed';

    protected $guarded = [];

    protected $casts = [
        'mark' => 'float',
        'success_mark' => 'float',
        'attempt_number' => 'integer',
        'exam_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    /**
     * سجل الطالب في المادة (يحدد الخطة/المستوى الذي يتبعه الاختبار)
     */
    public function studentSubject()
    {
        return $this->belongsTo(StudentSubject::class);
    }

    /**
     * آخر محاولة سرد تجريبي للطالب في نفس المادة ونفس الخطة
     */
    public function lastRecitationExam()
    {
        return $this->hasOne(RecitationExam::class, 'student_subject_id', 'student_subject_id')
            ->latestOfMany('attempt_number');
    }

    public function hasResult(): bool
    {
        return $this->mark !== null;
    }

    /**
     * بانتظار النتيجة، أو ناجح (العلامة ≥ علامة النجاح)، أو راسب
     */
    public function status(): string
    {
        if (!$this->hasResult()) {
            return self::PENDING;
        }

        return $this->mark >= $this->success_mark ? self::PASSED : self::FAILED;
    }
}
