<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'       => $this->id,
            'identity' => $this->identity,

            // الاسم الشخصي
            'fName'  => $this->fName,
            'sName'  => $this->sName,
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
            'dob' => $this->dob,

            // الجنس
            'gender'     => $this->gender,
            'genderText' => $this->gender_text ?? null,

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
            'contact' => [
                'phone'        => $this->phone,
                'whatsapp'     => $this->whatsapp,
                'has_whatsapp' => !is_null($this->whatsapp),
            ],

            // الموقع
            'location' => $this->location,

            // التواريخ
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // إحصائيات
            'enrollments_count' => $this->when(isset($this->enrollments_count), $this->enrollments_count),
            'attendances_count' => $this->when(isset($this->attendances_count), $this->attendances_count),

            // روابط
            'links' => [
                'self'        => url("/api/students/{$this->id}"),
                'mosque'      => url("/api/mosques/{$this->mosque_id}"),
                'guardian'    => url("/api/users/identity/{$this->guardian_id}"),
                'enrollments' => url("/api/enrollments?student_id={$this->id}"),
                'attendances' => url("/api/attendances?attendable_id={$this->id}&attendable_type=student"),
            ],
        ];
    }
}
