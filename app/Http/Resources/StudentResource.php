<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,

            // الاسم الشخصي
            'personal_names' => [
                'fName' => $this->fName,
                'sName' => $this->sName,
                'thName' => $this->thName,
                'family' => $this->family,
            ],

            // الاسم الكامل (محسوب آلياً)
            'full_name' => $this->full_name,

            // الاسم مع البادئة
            'full_name_with_prefix' => $this->whenLoaded('prefixName', function () {
                return ($this->prefixName->name ?? '') . ' ' . $this->full_name;
            }),

            // البادئة (من constants)
            'prefix' => $this->whenLoaded('prefixName', function () {
                return [
                    'id' => $this->prefix_name_id,
                    'name' => $this->prefixName->name ?? null,
                ];
            }),

            // تاريخ الميلاد
            // 'dob' => $this->dob ? $this->dob->format('Y-m-d') : null,
            // 'dob_formatted' => $this->dob ? $this->dob->format('d/m/Y') : null,
            // 'age' => $this->dob ? $this->dob->age : null,

            // معلومات المسجد
            'mosque' => $this->whenLoaded('mosque', function () {
                return [
                    'id' => $this->mosque_id,
                    'name' => $this->mosque->name ?? null,
                    'location' => $this->mosque->location ?? null,
                ];
            }),

            // الموقع
            'location' => $this->location,

            // الجنس
            'gender' => $this->gender,
            'gender_label' => $this->gender == 'male' ? 'ذكر' : 'أنثى',

            // الحالة الاجتماعية (من constants)
            'marital_status' => $this->whenLoaded('maritalStatus', function () {
                return [
                    'id' => $this->marital_status_id,
                    'name' => $this->maritalStatus->name ?? null,
                ];
            }),

            // الحالة المادية (من constants)
            'money_status' => $this->whenLoaded('moneyStatus', function () {
                return [
                    'id' => $this->money_status_id,
                    'name' => $this->moneyStatus->name ?? null,
                ];
            }),

            // معلومات ولي الأمر
            'guardian' => $this->whenLoaded('guardian', function () {
                return new UserResource($this->guardian);
                /* return [
                    'id' => $this->guardian->id ?? null,
                    'identity' => $this->guardian_id,
                    'name' => $this->guardian->name ?? null,
                    'phone' => $this->guardian->phone ?? null,
                    'email' => $this->guardian->email ?? null,
                ]; */
            }),

            // صلة القرابة مع ولي الأمر (من constants)
            'guardian_relation' => $this->whenLoaded('guardianRelation', function () {
                return [
                    'id' => $this->guardian_type_id,
                    'name' => $this->guardianRelation->name ?? null,
                ];
            }),

            // معلومات الاتصال
            'contact' => [
                'phone' => $this->phone,
                'whatsapp' => $this->whatsapp,
                'has_whatsapp' => !is_null($this->whatsapp),
            ],

            // التواريخ (إذا كانت موجودة في الجدول)
            // 'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            // 'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,

            // إحصائيات (إذا كانت محملة)
            'enrollments_count' => $this->when($this->enrollments_count !== null, $this->enrollments_count),
            'attendances_count' => $this->when($this->attendances_count !== null, $this->attendances_count),

            // روابط
            'links' => [
                'self' => url("/api/students/{$this->id}"),
                'mosque' => url("/api/mosques/{$this->mosque_id}"),
                'guardian' => url("/api/users/identity/{$this->guardian_id}"),
                'enrollments' => url("/api/enrollments?student_id={$this->id}"),
                'attendances' => url("/api/attendances?attendable_id={$this->id}&attendable_type=student"),
            ],
        ];
    }
}