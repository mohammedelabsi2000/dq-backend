<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HalaqaStudentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'halaqa' => [
                'id' => $this->halaqa->id ?? null,
                'name' => $this->halaqa->name ?? null, // عدل حسب عمود الاسم عندك
            ],
            'student' => [
                'id' => $this->student->id ?? null,
                'name' => $this->student->name ?? null, // عدل حسب عمود الاسم عندك
                'email' => $this->student->email ?? null, // اختياري
            ],
            'status' => [
                'id' => $this->status->id ?? null,
                'name' => $this->status->name ?? null,
            ],
            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];

    }
}
