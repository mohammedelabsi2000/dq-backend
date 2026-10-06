<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * مادة حالية للطالب (StudentSubject) مع المتبقي منها لإضافة إنجاز يومي.
 * يتوقع العلاقتين الوهميتين studentPlan و progress (من SubjectProgressService).
 */
class AvailableSubjectResource extends JsonResource
{
    public function toArray($request): array
    {
        $subject = $this->subject;
        $studentPlan = $this->studentPlan;
        $progress = $this->progress;

        return [
            'id' => $subject->id,
            'title' => $subject->title,
            'sub_title' => $subject->sub_title,
            'subject_type' => new ConstantResource($subject->subjectType),
            // اتجاه المادة في خطة الطالب (يحدد ترتيب الأجزاء) واتجاهات الأجزاء المخصَّصة
            'memorization_direction' => $progress['memorization_direction'],
            'juz_directions' => $progress['juz_directions'],
            'student_subject_id' => $this->id,

            'plan' => [
                'id' => $studentPlan->plan_id,
                'name' => $studentPlan->plan?->name,
                'is_main' => $studentPlan->is_main,
            ],
            'level' => [
                'id' => $this->level_id,
                'name' => $studentPlan->currentLevel?->name,
            ],

            'has_range' => $progress['has_range'],
            'total_ayahs_count' => $progress['total_ayahs_count'],
            'available_ayahs_count' => $progress['available_ayahs_count'],
            'is_completed' => $progress['is_completed'],

            // الجزء الذي يحفظ فيه الطالب حالياً واتجاهه
            'current_juz' => $progress['current_juz'],

            // نقطة البداية للإنجاز القادم (للعرض فقط - تُحسب تلقائياً عند الحفظ)
            'next_start' => $progress['next_start'],

            // أقصى نطاق للإنجاز القادم: من نقطة البداية حتى نهاية المادة أو حتى أول جزء مختلف الاتجاه
            'available_ranges' => $progress['available_range'],
            'available_surahs' => $progress['available_surahs'],
        ];
    }
}
