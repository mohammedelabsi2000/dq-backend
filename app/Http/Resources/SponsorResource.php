<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class SponsorResource extends JsonResource
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
            'name' => $this->name,
            'project_number' => $this->project_number,
            'follow_up_entity' => $this->follow_up_entity,
            'sponsorship_type' => $this->sponsorship_type,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'required_halaqat_male' => $this->required_halaqat_male,
            'required_halaqat_female' => $this->required_halaqat_female,
            'students_per_halaqa_male' => $this->students_per_halaqa_male,
            'students_per_halaqa_female' => $this->students_per_halaqa_female,
            'student_type_id' => $this->student_type_id,
            'student_type' => new ConstantResource($this->whenLoaded('studentType')),
            'student_type_other_note' => $this->student_type_other_note,
            'is_active' => $this->is_active,
            'notes' => $this->notes,
            'remaining_capacity_male' => $this->when(
                $this->relationLoaded('activeHalaqaSponsorships'),
                fn () => $this->remainingCapacityFor('ذكر')
            ),
            'remaining_capacity_female' => $this->when(
                $this->relationLoaded('activeHalaqaSponsorships'),
                fn () => $this->remainingCapacityFor('أنثى')
            ),
            'attachments' => ImageResource::collection($this->whenLoaded('attachments')),
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
