<?php

namespace App\Http\Resources;

use App\Models\Quran\Surah;
use Illuminate\Http\Resources\Json\JsonResource;

class DailyAchievementResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,

            // الطالب
            'student_id' => $this->student_id,
            'student' => new StudentResource($this->whenLoaded('student')),

            // المعلم
            'teacher_id' => $this->teacher_id,
            'teacher' => new UserResource($this->whenLoaded('teacher')),

            'subject_id' => $this->subject_id,
            'subject'    => new SubjectResource($this->whenLoaded('subject')),

            // التاريخ
            'date' => $this->date?->format('Y-m-d'),

            // السور والآيات
            'from_surah' => Surah::find($this->from_surah),
            'from_ayah' => $this->from_ayah,
            'to_surah' => Surah::find($this->to_surah),
            'to_ayah' => $this->to_ayah,
            // 'ayah_count' => $this->ayah_count,

            // اتجاه الحفظ
            'memorization_direction' => $this->memorization_direction?->value,
            'memorization_direction_label' => $this->memorization_direction?->label(),

            // محسوبة تلقائياً
            'ayahs_count' => $this->ayahs_count,
            'pages_count' => (float) $this->pages_count,

            // نوع الحفظ
            'achievement_type' => $this->achievement_type,
            'achievement_type_label' => $this->achievement_type?->getLabel(),

            // درجة التقييم
            'evaluation_grade' => $this->evaluation_grade,
            'evaluation_grade_label' => $this->evaluation_grade?->getLabel(),

            // حالة الإنجاز
            'achievement_status' => $this->achievement_status,
            'achievement_status_label' => $this->achievement_status?->getLabel(),

            // عدد الأخطاء
            'mistakes_count' => $this->mistakes_count,

            // الملاحظات
            'notes' => $this->notes,

            // وقت التسجيل
            'recorded_at' => $this->recorded_at?->format('Y-m-d H:i:s'),

            // التواريخ
            // 'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            // 'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // روابط
            // 'links' => [
            //     'self' => url("/api/daily-achievements/{$this->id}"),
            //     'student' => url("/api/students/{$this->student_id}"),
            //     'teacher' => $this->teacher_id ? url("/api/users/{$this->teacher_id}") : null,
            // ],
        ];
    }
}
