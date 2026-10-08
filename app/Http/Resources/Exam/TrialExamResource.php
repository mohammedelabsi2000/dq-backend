<?php

namespace App\Http\Resources\Exam;

use App\Http\Resources\StudentResource;
use App\Http\Resources\SubjectResource;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;

class TrialExamResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $lastRecitationExam = $this->lastRecitationExam;
        // $latestTrialExam = $this->latestTrialExam;

        return [
            'id' => $this->id,
            'student_id' => $this->student_id,
            'student' => (new StudentResource($this->student))->simple(),
            'subject_id' => $this->subject_id,
            'subject' => (new SubjectResource($this->subject))->simple(),
            'exam_level' => $this->exam_level(),
            'sard_status' => $this->recitation_status(),
            'errors_count' => $lastRecitationExam?->errors_count,
            'alerts_count' => $lastRecitationExam?->alerts_count,
            'errors_count_allowed' => $lastRecitationExam?->errors_count_allowed,
            'alerts_count_allowed' => $lastRecitationExam?->alerts_count_allowed,
            'exam_status' => $this->exam_status(),
            'mark' => $this->mark,
            'success_mark' => $this->success_mark,
            'exam_failur_counter' => $this->attempt_number - 1,
            'sard_failur_counter' => $lastRecitationExam?->attempt_number - 1,
            'exam_date' => $this->exam_date,
            // 'notes' => $latestTrialExam?->notes,
        ];
    }

    private function recitation_status()
    {
        $lastRecitationExam = $this->lastRecitationExam;
        if ($lastRecitationExam->errors_count === null || $lastRecitationExam->alerts_count === null) {
            return 'pending';
        }
        if (
            $lastRecitationExam->errors_count <= $lastRecitationExam->errors_count_allowed
            && $lastRecitationExam->alerts_count <= $lastRecitationExam->alerts_count_allowed
        ) {
            return 'passed';
        } else {
            return 'failed';
        }
    }

    private function exam_status()
    {
        if ($this->mark === null) {
            return 'pending';
        }
        if ($this->mark >= $this->success_mark) {
            return 'passed';
        } else {
            return 'failed';
        }
    }

    private function exam_level()
    {
        if ($this->recitation_status() === 'pending' || $this->recitation_status() === 'failed') {
            return 'السرد التجريبي';
        } elseif ($this->exam_status() === 'pending' || $this->exam_status() === 'failed') {
            return 'الاختبار التجريبي';
        } else {
            return 'الاختبار النهائي';
        }
    }
}
