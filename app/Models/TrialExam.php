<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrialExam extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function lastRecitationExam()
    {
        return $this->hasOne(RecitationExam::class, 'student_id', 'student_id')
            ->where('subject_id', $this->subject_id)
            ->orderByDesc('exam_date')
            ->latestOfMany('attempt_number');
    }

    public function studentSubject()
    {
        return $this->hasMany(StudentSubject::class, 'student_id', 'student_id')
            ->where('subject_id', $this->subject_id)
            ->orderByDesc('from_date');
    }
}
