<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * تنسيب طالب (في مادة ضمن خطة محددة) للجنة اختبار نهائي
 */
class ExamCommitteeStudent extends Model
{
    use HasFactory, SoftDeletes;

    public static $usesAudit = true;

    protected $guarded = [];

    public function committee()
    {
        return $this->belongsTo(ExamCommittee::class, 'exam_committee_id');
    }

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
}
