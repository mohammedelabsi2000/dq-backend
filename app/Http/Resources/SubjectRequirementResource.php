<?php

namespace App\Http\Resources;

use App\Enums\SuccessValueType;
use Illuminate\Http\Resources\Json\JsonResource;

class SubjectRequirementResource extends JsonResource
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
            'subject_id' => $this->subject_id,
            'success_value_type' => $this->success_value_type,
            'success_value' => $this->success_value,
            'weight' => $this->weight,
            'success_value_type_label' => SuccessValueType::tryFrom($this->success_value_type)?->label(),
        ];
    }
}
