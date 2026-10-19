<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StudentSubject extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'from_date' => 'date',
        'to_date' => 'date',
        'grade_date' => 'date',
    ];

    /**
     * آخر سجل نجح فيه الطالب في نفس المادة (في أي خطة)، إن وجد
     */
    public static function previousPass(int $studentId, int $subjectId, ?int $exceptId = null): ?self
    {
        return static::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->when($exceptId, fn($q) => $q->where('id', '!=', $exceptId))
            ->whereHas('resultStatus', fn($q) => $q->where('const_key', 'passed'))
            ->with(['level.plan', 'resultStatus'])
            ->latest('id')
            ->first();
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function plan()
    {
        return $this->hasOneThrough(Plan::class, Level::class, 'id', 'id', 'level_id', 'plan_id');
    }

    public function resultStatus()
    {
        return $this->belongsTo(Constant::class, 'result_status_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function studentLevel()
    {
        return $this->belongsTo(StudentLevel::class);
    }

    public function levelTrack()
    {
        return $this->belongsTo(LevelTrack::class);
    }

    /**
     * تنسيب الطالب في هذه المادة للجنة اختبار نهائي (إن وجد)
     */
    public function examCommitteeStudent()
    {
        return $this->activeCommitteeAssignment();
    }

    /**
     * كل تنسيبات الطالب في هذه المادة للجان (بما فيها لجان سابقة رسب أو غاب فيها)
     */
    public function examCommitteeStudents()
    {
        return $this->hasMany(ExamCommitteeStudent::class);
    }

    /**
     * التنسيب الحالي: لجنة لم تصل نتيجة الطالب منها بعد
     */
    public function activeCommitteeAssignment()
    {
        return $this->hasOne(ExamCommitteeStudent::class)
            ->whereNotExists(function ($q) {
                $q->selectRaw(1)
                    ->from('final_exams')
                    ->whereColumn('final_exams.student_subject_id', 'exam_committee_students.student_subject_id')
                    ->whereColumn('final_exams.exam_committee_id', 'exam_committee_students.exam_committee_id')
                    ->whereNull('final_exams.deleted_at');
            });
    }

    public function finalExams()
    {
        return $this->hasMany(FinalExam::class);
    }

    /**
     * المرشحون للاختبار النهائي: ما زالوا يدرسون المادة واجتازوا الاختبار التجريبي.
     * افتراضياً يُستثنى من نُسِّب للجنة.
     */
    public function scopeFinalExamCandidates($query, bool $unassignedOnly = true)
    {
        // whereHas('student') يطبّق قاعدة رؤية الجنس: لا يظهر طالب لا يحق للمستخدم رؤيته
        $query->whereHas('student')
            ->whereNull('student_subjects.to_date')
            ->where(function ($q) {
                $q->whereNull('result_status_id')
                    ->orWhereHas('resultStatus', fn($q) => $q->where('const_key', 'in_progress'));
            })
            // الاختبار الناجح هو دائماً آخر محاولة: لا تُفتح محاولة جديدة بعد النجاح
            ->whereHas('trialExams', fn($q) => $q->whereColumn('mark', '>=', 'success_mark'));

        // من رسب أو غاب في لجنة سابقة يعود مرشحاً؛ المستثنى فقط من ينتظر نتيجة لجنته الحالية
        if ($unassignedOnly) {
            $query->whereDoesntHave('activeCommitteeAssignment');
        }

        return $query;
    }

    public function recitationExams()
    {
        return $this->hasMany(RecitationExam::class);
    }

    public function trialExams()
    {
        return $this->hasMany(TrialExam::class);
    }

    /**
     * آخر محاولة سرد تجريبي للطالب في هذه المادة ضمن هذه الخطة
     */
    public function latestRecitationExam()
    {
        return $this->hasOne(RecitationExam::class)->latestOfMany('attempt_number');
    }

    /**
     * آخر محاولة اختبار تجريبي للطالب في هذه المادة ضمن هذه الخطة
     */
    public function latestTrialExam()
    {
        return $this->hasOne(TrialExam::class)->latestOfMany('attempt_number');
    }
}
