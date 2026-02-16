<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'book_name' => $this->book_name,
            'hours' => $this->hours,
            'max_score' => $this->max_score,

            // المسار المرتبط
            'track' => $this->whenLoaded('track', function () {
                return [
                    'id' => $this->track->id,
                    'name' => $this->track->name,
                    'description' => $this->track->description,
                ];
            }),

            // إحصائيات إضافية (مثلاً عدد الطلاب المسجلين للدورة إذا تمت التحميل)
            'students_count' => $this->whenLoaded('students_count', $this->students_count),

            // التواريخ
            'created_at' => $this->created_at ? $this->created_at->format('Y-m-d H:i:s') : null,
            'updated_at' => $this->updated_at ? $this->updated_at->format('Y-m-d H:i:s') : null,

            // روابط API
            'links' => [
                'self' => url("/api/courses/{$this->id}"),
                'track' => $this->track ? url("/api/tracks/{$this->track->id}") : null,
            ],
        ];
    }
}
