<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id'       => $this->id,
            'identity' => $this->identity,

            // الاسم الشخصي
            'personal_names' => [
                'fName'  => $this->fName,
                'sName'  => $this->sName,
                'thName' => $this->thName,
                'family' => $this->family,
            ],

            // الاسم الكامل
            'full_name' => $this->full_name,

            // الاسم مع البادئة
            'full_name_with_prefix' => $this->whenLoaded('prefixName', function () {
                return trim(($this->prefixName->name ?? '') . ' ' . $this->full_name);
            }),

            // البادئة
            'prefix' => $this->whenLoaded('prefixName', function () {
                return [
                    'id'   => $this->prefix_name_id,
                    'name' => $this->prefixName->name ?? null,
                ];
            }),

            // تاريخ الميلاد
            'dob'           => $this->dob,
            // 'dob_formatted' => $this->dob ? \Carbon\Carbon::parse($this->dob)->format('d/m/Y') : null,
            // 'age'           => $this->dob ? \Carbon\Carbon::parse($this->dob)->age : null,

            // المسجد
            'mosque' => $this->whenLoaded('mosque', function () {
                return [
                    'id'       => $this->mosque_id,
                    'name'     => $this->mosque->name ?? null,
                    'location' => $this->mosque->location ?? null,
                ];
            }),

            // الموقع
            'location' => $this->location,

            // الجنس
            'gender' => $this->gender,

            // الحالة الاجتماعية
            'marital_status' => $this->whenLoaded('maritalStatus', function () {
                return [
                    'id'   => $this->marital_status_id,
                    'name' => $this->maritalStatus->name ?? null,
                ];
            }),

            // الحالة المادية
            'money_status' => $this->whenLoaded('moneyStatus', function () {
                return [
                    'id'   => $this->money_status_id,
                    'name' => $this->moneyStatus->name ?? null,
                ];
            }),

            // ولي الأمر
            'guardian' => $this->whenLoaded('guardian', function () {
                return new UserResource($this->guardian);
            }),

            // صلة القرابة مع ولي الأمر
            'guardian_relation' => $this->whenLoaded('guardianType', function () {
                return [
                    'id'   => $this->guardian_type_id,
                    'name' => $this->guardianType->name ?? null,
                ];
            }),

            // معلومات الاتصال
            'contact' => [
                'phone'        => $this->phone,
                'whatsapp'     => $this->whatsapp,
                'has_whatsapp' => !is_null($this->whatsapp),
            ],

            // التواريخ
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),

            // إحصائيات
            'enrollments_count' => $this->when(
                isset($this->enrollments_count),
                $this->enrollments_count
            ),
            'attendances_count' => $this->when(
                isset($this->attendances_count),
                $this->attendances_count
            ),

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
