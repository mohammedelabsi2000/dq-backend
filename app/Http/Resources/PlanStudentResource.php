<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PlanStudentResource extends JsonResource
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
            'plan' => new PlanResource($this),

            'students' => StudentResource::collection($this->students),

            // 'student_ids' => $this->students->pluck('id'),

        ];
    }
}
