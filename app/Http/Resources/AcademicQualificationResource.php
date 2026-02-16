<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcademicQualificationResource extends JsonResource
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

            // العلاقات مع constants
            'academic_degree' => [
                'id' => $this->academic_degree_id,
                'name' => $this->whenLoaded('academicDegree', function () {
                    return $this->academicDegree->name ?? null;
                }),
                'value' => $this->whenLoaded('academicDegree', function () {
                    return $this->academicDegree->value ?? null;
                }),
                'type' => $this->whenLoaded('academicDegree', function () {
                    return $this->academicDegree->type ?? 'academic_degree';
                }),
            ],

            'major' => [
                'id' => $this->major_id,
                'name' => $this->whenLoaded('major', function () {
                    return $this->major->name ?? null;
                }),
                'value' => $this->whenLoaded('major', function () {
                    return $this->major->value ?? null;
                }),
                'type' => $this->whenLoaded('major', function () {
                    return $this->major->type ?? 'major';
                }),
            ],

            // معلومات الشخص (morph relation)
            'person' => [
                'id' => $this->person_id,
                'type' => $this->person_type,
                'name' => $this->whenLoaded('person', function () {
                    if ($this->person) {
                        // افترض أن لديك اسم أو طريقة لعرض اسم الشخص
                        return $this->person->name ??
                            $this->person->full_name ??
                            $this->person->title ?? null;
                    }
                    return null;
                }),
                'data' => $this->whenLoaded('person', function () {
                    // إذا أردت إرجاع كامل بيانات الشخص حسب نوعه
                    return $this->getPersonData();
                }),
            ],

            // الحقول الأساسية
            'detail' => $this->detail,
            'date_graduate' => $this->date_graduate ? $this->date_graduate->format('Y-m-d') : null,
            'date_graduate_formatted' => $this->date_graduate ? $this->date_graduate->format('d/m/Y') : null,
            'certificate_link' => $this->certificate_link,
            'educational_institution' => $this->educational_institution,
            'notes' => $this->notes,

            // روابط إضافية
            /* 'links' => [
                'self' => route('api.academic-qualifications.show', $this->id),
                'certificate' => $this->certificate_link ? asset('storage/' . $this->certificate_link) : null,
            ], */
        ];
    }

    /**
     * Get person data based on person type
     */
    private function getPersonData()
    {
        if (!$this->person) {
            return null;
        }

        switch (class_basename($this->person)) {
            case 'Student':
                return new StudentResource($this->person);
            case 'User':
                return new UserResource($this->person);
            default:
                return $this->person->toArray();
        }
    }
}