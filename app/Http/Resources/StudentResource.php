<?php

namespace App\Http\Resources;

use App\Models\Constant;
use App\Models\Level;
use App\Models\Quran\Surah;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'identity' => $this->identity,

            // الاسم الشخصي
            'fName' => $this->fName,
            'sName' => $this->sName,
            'thName' => $this->thName,
            'family' => $this->family,

            // الاسم الكامل
            'full_name' => trim(preg_replace('/\s+/', ' ', $this->full_name)),

            // الاسم مع البادئة
            'full_name_with_prefix' => $this->whenLoaded('prefixName', function () {
                return trim(($this->prefixName->name ?? '') . ' ' . $this->full_name);
            }),

            // البادئة
            'prefix_name' => new ConstantResource($this->whenLoaded('prefixName')),

            // تاريخ الميلاد
            'dob' => $this->dob?->format('Y-m-d'),

            // الجنس
            'gender' => $this->gender?->label(),
            'genderText' => $this->gender_text,

            // المسجد
            'mosque' => new MosqueResource($this->whenLoaded('mosque')),

            // الحالة الاجتماعية
            'marital_status' => new ConstantResource($this->whenLoaded('maritalStatus')),

            // الحالة المادية
            'money_status' => new ConstantResource($this->whenLoaded('moneyStatus')),

            // ولي الأمر
            'guardian' => new UserResource($this->whenLoaded('guardian')),

            // صلة القرابة مع ولي الأمر
            'guardian_relation' => new ConstantResource($this->whenLoaded('guardianType')),

            // معلومات الاتصال
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'has_whatsapp' => !is_null($this->whatsapp),

            // الموقع
            'location' => $this->location,

            // الاعتماد
            'is_approved' => $this->is_approved,
            'approval' => [
                'is_approved'      => $this->is_approved,
                'status'           => $this->when(
                    $this->relationLoaded('approvalRequest'),
                    fn() => $this->approvalRequest?->status?->label()
                ),
                'rejection_reason' => $this->when(
                    $this->relationLoaded('approvalRequest'),
                    fn() => $this->approvalRequest?->rejection_reason
                ),
            ],

            // Previous achievement
            'memorized_juz' => $this->memorized_juz ?? null,
            'memorized_juz_array' => $this->memorized_juz ? array_map('intval', explode(',', $this->memorized_juz)) : null,
            'completed_juz' => $this->memorized_juz ?? null,
            'completed_juz_array' => $this->completed_juz ? array_map('intval', explode(',', $this->completed_juz)) : null,
            'surah_id' => $this->surah_id ?? null,
            'last_surah_label' => Surah::find($this->surah_id)?->name_ar,
            'end_aya' => $this->end_aya ?? null,
            'memorization_direction' => $this->memorization_direction?->value,
            'memorization_direction_label' => $this->memorization_direction?->label(),

            // الحلقات
            'halaqas' => $this->whenLoaded('halaqas', function () {
                return $this->halaqas->map(function ($halaqa) {
                    return [
                        'id' => $halaqa->id,
                        'name' => $halaqa->name,
                        'from_date' => $halaqa->pivot->from_date,
                        'to_date' => $halaqa->pivot->to_date,
                        'enrollment_status_id' => $halaqa->pivot->enrollment_status_id,
                        'enrollment_status' => $halaqa->pivot->enrollment_status_id ?
                            Constant::find($halaqa->pivot->enrollment_status_id)?->name : null,
                    ];
                });
            }),
            'halaqa_student_id' => $this->pivot?->id,
            'from_date' => $this->pivot?->from_date,
            'to_date' => $this->pivot?->to_date,
            'enrollment_status' => new ConstantResource(
                Constant::find($this->pivot?->enrollment_status_id)
            ),

            'current_halaqa' => $this->whenLoaded('halaqas', function () {
                $current = $this->halaqas->firstWhere('pivot.to_date', null);
                if ($current) {
                    return [
                        'id' => $current->id,
                        'name' => $current->name,
                        'from_date' => $current->pivot->from_date,
                        'enrollment_status' => $current->pivot->enrollment_status_id ?
                            Constant::find($current->pivot->enrollment_status_id)?->name : null,
                    ];
                }
                return null;
            }),

            // التواريخ
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            'deleted_at' => $this->deleted_at?->format('Y-m-d H:i:s'),

            // إحصائيات
            'enrollments_count' => $this->when(isset($this->enrollments_count), $this->enrollments_count),

            'plan_pivots' => $this->whenPivotLoaded('student_plans', function () {
                return [
                    'starting_level_id' => $this->pivot->starting_level_id,
                    // 'starting_level' => $this->when($this->pivot->starting_level_id, fn() => new LevelResource(Level::find($this->pivot->starting_level_id))),

                    'current_level_id' => $this->pivot->current_level_id,
                    // 'current_level' => $this->when($this->pivot->current_level_id, fn() => new LevelResource(Level::find($this->pivot->current_level_id))),

                    'from_date' => $this->pivot->from_date,
                    'to_date' => $this->pivot->to_date,

                    'is_main' => $this->pivot->is_main,

                    'status' => $this->pivot->status,
                    'status_label' => $this->pivot->status,

                    'is_active' => is_null($this->pivot->to_date),

                    'notes' => $this->pivot->notes,
                ];
            }),
        ];
    }
}
