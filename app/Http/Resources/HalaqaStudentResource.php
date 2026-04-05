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
            'halaqa' => new HalaqaResource($this->whenLoaded('halaqa')),


            'student' => new StudentResource($this->whenLoaded('student')),


            'enrollment_status' => new ConstantResource($this->whenLoaded('enrollment_status')),

            'from_date' => $this->from_date,
            'to_date' => $this->to_date,
            'created_at' => optional($this->created_at)->toDateTimeString(),
            'updated_at' => optional($this->updated_at)->toDateTimeString(),
        ];
    }
}
