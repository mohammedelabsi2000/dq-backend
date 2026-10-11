<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class RecitationExam extends Model
{
    use HasFactory, SoftDeletes;

    public const PENDING = 'pending';
    public const PASSED = 'passed';
    public const FAILED = 'failed';

    protected $guarded = [];

    protected $casts = [
        'errors_count' => 'integer',
        'alerts_count' => 'integer',
        'errors_count_allowed' => 'integer',
        'alerts_count_allowed' => 'integer',
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
     * سجل الطالب في المادة (يحدد الخطة/المستوى الذي يتبعه السرد)
     */
    public function studentSubject()
    {
        return $this->belongsTo(StudentSubject::class);
    }

    public function hasResult(): bool
    {
        return $this->errors_count !== null && $this->alerts_count !== null;
    }

    /**
     * بانتظار النتيجة، أو ناجح (الأخطاء والتنبيهات ضمن المسموح)، أو راسب
     */
    public function status(): string
    {
        if (!$this->hasResult()) {
            return self::PENDING;
        }

        return $this->errors_count <= $this->errors_count_allowed && $this->alerts_count <= $this->alerts_count_allowed
            ? self::PASSED
            : self::FAILED;
    }
}
