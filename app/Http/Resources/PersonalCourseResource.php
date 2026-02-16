<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PersonalCourseResource extends JsonResource
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
            
            // معلومات الدورة
            'course_name' => $this->course_name,
            'notes' => $this->notes,
            
            // معلومات الساعات
            'hours' => $this->hours,
            'hours_info' => [
                'value' => $this->hours,
                'label' => $this->hours ? $this->hours . ' ساعة' : null,
                'days' => $this->hours ? round($this->hours / 8, 1) . ' يوم' : null,
            ],
            
            // مقدم الدورة
            'provider' => $this->provider,
            'place' => $this->place,
            
            // رابط الشهادة
            'certificate_link' => $this->certificate_link,
            'certificate_url' => $this->certificate_link ? url('storage/' . $this->certificate_link) : null,
            'has_certificate' => !is_null($this->certificate_link),
            
            // نوع الدورة (من constants)
            'type' => $this->whenLoaded('type', function() {
                return [
                    'id' => $this->type_id,
                    'name' => $this->type->name ?? null,
                    'color' => $this->getCourseTypeColor($this->type->name ?? ''),
                ];
            }),
            
            // معلومات الشخص (polymorphic)
            'person' => [
                'id' => $this->person_id,
                'type' => $this->person_type,
                'type_name' => $this->getPersonTypeName(),
                'data' => $this->whenLoaded('person', function() {
                    return $this->getPersonData();
                }),
            ],
            
            // إحصائيات إضافية (إذا كانت محملة)
            'related_courses_count' => $this->when($this->related_courses_count !== null, $this->related_courses_count),
            
            // التواريخ
            // 'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            // 'created_at_formatted' => $this->created_at ? $this->created_at->format('d/m/Y') : null,
            // 'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,
            
            // روابط
            'links' => [
                'self' => url("/api/personal-courses/{$this->id}"),
                'person' => $this->getPersonLink(),
                'certificate' => $this->certificate_link ? url('storage/' . $this->certificate_link) : null,
            ],
            
            // ميتا بيانات
            'meta' => [
                'can_delete' => true,
                'can_edit' => true,
            ],
        ];
    }

    /**
     * الحصول على اسم نوع الشخص
     */
    private function getPersonTypeName(): string
    {
        $types = [
            'App\\Models\\User' => 'مستخدم',
            'App\\Models\\Student' => 'طالب',
            'User' => 'مستخدم',
            'Student' => 'طالب',
        ];
        
        $class = class_basename($this->person_type);
        
        return $types[$this->person_type] ?? $types[$class] ?? $class;
    }

    /**
     * الحصول على بيانات الشخص حسب نوعه
     */
    private function getPersonData(): ?array
    {
        if (!$this->person) {
            return null;
        }

        switch (class_basename($this->person)) {
            case 'User':
                return [
                    'name' => $this->person->name,
                    'email' => $this->person->email,
                    'identity' => $this->person->identity ?? null,
                    'phone' => $this->person->phone ?? null,
                ];
                
            case 'Student':
                return [
                    'name' => $this->person->full_name ?? $this->person->name,
                    'full_name' => $this->person->full_name,
                    'student_number' => $this->person->student_number ?? null,
                    'gender' => $this->person->gender ?? null,
                    'phone' => $this->person->phone ?? null,
                ];
                
            default:
                return [
                    'name' => $this->person->name ?? 'N/A',
                    'id' => $this->person->id,
                ];
        }
    }

    /**
     * الحصول على رابط الشخص
     */
    private function getPersonLink(): ?string
    {
        if (!$this->person_id || !$this->person_type) {
            return null;
        }

        $type = class_basename($this->person_type);
        
        $routes = [
            'User' => 'users',
            'Student' => 'students',
        ];
        
        $route = $routes[$type] ?? strtolower($type) . 's';
        
        return url("/api/{$route}/{$this->person_id}");
    }

    /**
     * الحصول على لون نوع الدورة
     */
    private function getCourseTypeColor(string $type): string
    {
        $colors = [
            'تقنية' => 'blue',
            'لغة' => 'green',
            'إدارية' => 'purple',
            'مهنية' => 'orange',
            'دينية' => 'teal',
            'تطوير ذاتي' => 'pink',
        ];
        
        foreach ($colors as $key => $color) {
            if (str_contains($type, $key)) {
                return $color;
            }
        }
        
        return 'gray';
    }
}