<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceResource extends JsonResource
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

            // معلومات الحضور القابل (polymorphic)
            'attendable' => [
                'id' => $this->attendable_id,
                'type' => $this->attendable_type,
                'type_name' => $this->getAttendableTypeName(),
                'data' => $this->whenLoaded('attendable', function () {
                    return $this->getAttendableData();
                }),
            ],

            // معلومات الحلقة
            'halaqa' => [
                'id' => $this->halaqa_id,
                'name' => $this->whenLoaded('halaqa', function () {
                    return $this->halaqa->name ?? null;
                }),
                'description' => $this->whenLoaded('halaqa', function () {
                    return $this->halaqa->description ?? null;
                }),
            ],

            // تاريخ الحضور
            'date' => $this->date ? $this->date->format('Y-m-d') : null,
            'date_formatted' => $this->date ? $this->date->format('d/m/Y') : null,
            'day_name' => $this->date ? $this->date->format('l') : null,
            'day_name_ar' => $this->getArabicDayName(),

            // حالة الحضور (من constants)
            'status' => [
                'id' => $this->status_id,
                'name' => $this->whenLoaded('status', function () {
                    return $this->status->name ?? null;
                }),
                'color' => $this->whenLoaded('status', function () {
                    return $this->getStatusColor($this->status->name ?? '');
                }),
                'type' => $this->whenLoaded('status', function () {
                    return $this->status->constantType->name ?? null;
                }),
            ],

            // ملاحظات
            'notes' => $this->notes,

            // التواريخ
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'created_at_formatted' => $this->created_at ? $this->created_at->format('d/m/Y h:i A') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,

            // روابط
            'links' => [
                'self' => url("/api/attendances/{$this->id}"),
                'halaqa' => url("/api/halaqas/{$this->halaqa_id}"),
                'attendable' => $this->getAttendableLink(),
            ],
        ];
    }

    /**
     * الحصول على اسم نوع الحضور
     */
    private function getAttendableTypeName(): string
    {
        $types = [
            'App\\Models\\User' => 'محفظ',
            'App\\Models\\Student' => 'طالب',
            'User' => 'محفظ',
            'Student' => 'طالب',
        ];

        $class = class_basename($this->attendable_type);

        return $types[$this->attendable_type] ?? $types[$class] ?? $class;
    }

    /**
     * الحصول على بيانات الحضور حسب نوعه
     */
    private function getAttendableData(): ?array
    {
        if (!$this->attendable) {
            return null;
        }

        // بناءً على نوع attendable
        switch (class_basename($this->attendable)) {
            case 'User':
                return [
                    'name' => $this->attendable->name,
                    'email' => $this->attendable->email,
                    'username' => $this->attendable->username ?? null,
                ];

            case 'Student':
                return [
                    'name' => $this->attendable->name,
                    'full_name' => $this->attendable->full_name ?? $this->attendable->name,
                    'student_number' => $this->attendable->student_number ?? null,
                    'class' => $this->attendable->class ?? null,
                ];

            default:
                return [
                    'name' => $this->attendable->name ?? 'N/A',
                ];
        }
    }

    /**
     * الحصول على رابط الحضور
     */
    private function getAttendableLink(): ?string
    {
        if (!$this->attendable_id || !$this->attendable_type) {
            return null;
        }

        $type = class_basename($this->attendable_type);

        $routes = [
            'User' => 'users',
            'Student' => 'students',
        ];

        $route = $routes[$type] ?? strtolower($type) . 's';

        return url("/api/{$route}/{$this->attendable_id}");
    }

    /**
     * الحصول على لون الحالة
     */
    private function getStatusColor(string $status): string
    {
        $colors = [
            'حاضر' => 'green',
            'غائب' => 'red',
            'متأخر' => 'orange',
            'إجازة' => 'blue',
            'غياب بعذر' => 'yellow',
        ];

        foreach ($colors as $key => $color) {
            if (str_contains($status, $key)) {
                return $color;
            }
        }

        return 'gray';
    }

    /**
     * الحصول على اسم اليوم بالعربية
     */
    private function getArabicDayName(): ?string
    {
        if (!$this->date) {
            return null;
        }

        $days = [
            'Sunday' => 'الأحد',
            'Monday' => 'الإثنين',
            'Tuesday' => 'الثلاثاء',
            'Wednesday' => 'الأربعاء',
            'Thursday' => 'الخميس',
            'Friday' => 'الجمعة',
            'Saturday' => 'السبت',
        ];

        $dayName = $this->date->format('l');

        return $days[$dayName] ?? $dayName;
    }
}