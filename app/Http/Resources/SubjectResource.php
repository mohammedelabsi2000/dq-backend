<?php

namespace App\Http\Resources;

use App\Models\Quran\CustomJuz;
use App\Models\Quran\Juz;
use App\Models\Quran\Surah;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            // 'track_id'    => $this->track_id,
            'subject_type_id' => $this->subject_type_id,
            'custom_juz_id' => json_decode($this->custom_juz_id),
            // 'custom_juz_labels' => CustomJuz::find(json_decode($this->custom_juz_id))->pluck('name')->toArray(),
            'memorization_direction' => $this->memorization_direction,
            // 'memorization_direction_label' => $this->memorization_direction?->label,

            'title' => $this->title,
            'sub_title' => $this->sub_title,

            // Numeric arrays stored as JSON in the database, so we decode them before returning
            // 'juzs' => json_decode($this->juzs),
            // 'juz_labels' => Juz::find(json_decode($this->juzs))->pluck('name')->toArray(),
            'surahs' => json_decode($this->surahs),
            'surah_labels' => Surah::find(json_decode($this->surahs))->pluck('name_ar')->toArray(),
            'verses' => json_decode($this->verses),
            'pages' => json_decode($this->pages),

            // 'description' => $this->description,
            'notes' => $this->notes,
            'created_at' => $this->created_at?->toDateTimeString(),

            // 'track'       => new TrackResource($this->whenLoaded('track')),
            // 'custom_juz' => new CustomJuzResource($this->whenLoaded('customJuz')),
            'subject_type' => new ConstantResource($this->whenLoaded('subjectType')),
            'subject_requirements' => SubjectRequirementResource::collection($this->whenLoaded('subjectRequirements')),
            'standard_department_id' => $this->standard_department_id,
            'standard_department_name' => $this->standard_department_name,
            'gender' => $this->gender === 'ذكر' ? 'male' : ($this->gender === 'أنثى' ? 'female' : null),
            'success_mark' => $this->success_mark,
            'alerts_count' => $this->alerts_count,
            'errors_count' => $this->errors_count,
            'standard_pass_mark' => $this->standard_pass_mark,
            'standard_subject_id' => $this->standard_subject_id,
            // 'order' => $this->when(isset($this->pivot->order), $this->pivot->order),
            // 'weight' => $this->when(isset($this->pivot->weight), (float) $this->pivot->weight),
            'student_subject' => $this->when(
                $this->relationLoaded('currentStudentSubject') && $this->currentStudentSubject,
                fn() => new StudentSubjectResource($this->currentStudentSubject)
            ),
        ];
    }
}
