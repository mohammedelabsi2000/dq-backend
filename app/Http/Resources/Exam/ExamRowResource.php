<?php

namespace App\Http\Resources\Exam;

use App\Http\Resources\StudentResource;
use App\Http\Resources\SubjectResource;
use App\Models\RecitationExam;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * صف موحّد للسرد التجريبي (RecitationExam) والاختبار التجريبي (TrialExam) لعرضهما في واجهة واحدة.
 * الحقول التي لا تخص نوع الصف ترجع null، و result_fields تحدد ما يُدخل كنتيجة لهذا الصف.
 */
class ExamRowResource extends JsonResource
{
    public const TYPE_RECITATION = 'trial_recitation';
    public const TYPE_EXAM = 'trial_exam';

    public function toArray($request)
    {
        $isRecitation = $this->resource instanceof RecitationExam;
        $status = $this->status();

        return [
            'id' => $this->id,

            // نوع الصف: سرد تجريبي أو اختبار تجريبي
            'type' => $isRecitation ? self::TYPE_RECITATION : self::TYPE_EXAM,
            'type_label' => $isRecitation ? 'سرد تجريبي' : 'اختبار تجريبي',

            'student_id' => $this->student_id,
            'student' => $this->student ? (new StudentResource($this->student))->simple() : null,
            'subject_id' => $this->subject_id,
            'subject' => $this->subject ? (new SubjectResource($this->subject))->simple() : null,
            'student_subject_id' => $this->student_subject_id,

            'attempt_number' => $this->attempt_number,
            // عدد مرات الرسوب السابقة في هذا النوع
            'failure_counter' => $this->attempt_number - 1,

            // pending (بانتظار النتيجة) | passed | failed
            'status' => $status,
            'status_label' => ['pending' => 'بانتظار النتيجة', 'passed' => 'ناجح', 'failed' => 'راسب'][$status],

            // ما يُدخل كنتيجة لهذا الصف: السرد ← الأخطاء والتنبيهات، الاختبار ← العلامة فقط
            'result_fields' => $isRecitation ? ['errors_count', 'alerts_count'] : ['mark'],

            // نتيجة السرد (null في صف الاختبار)
            'errors_count' => $isRecitation ? $this->errors_count : null,
            'alerts_count' => $isRecitation ? $this->alerts_count : null,
            'errors_count_allowed' => $isRecitation ? $this->errors_count_allowed : null,
            'alerts_count_allowed' => $isRecitation ? $this->alerts_count_allowed : null,

            // نتيجة الاختبار (null في صف السرد)
            'mark' => $isRecitation ? null : $this->mark,
            'success_mark' => $isRecitation ? null : $this->success_mark,

            'exam_date' => $this->exam_date?->format('Y-m-d'),
            'notes' => $this->notes,
        ];
    }
}
