<?php

namespace App\Http\Resources\Certificate;

use Illuminate\Http\Resources\Json\JsonResource;

class CertificateResource extends JsonResource
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
            'person_type' => $this->person_type,
            'person_id' => $this->person_id,
            'academic_degree_id' => $this->academic_degree_id,
            'major_id' => $this->major_id,
            'certificate_number' => $this->certificate_number,
            'issue_date' => $this->issue_date,
            'expiry_date' => $this->expiry_date,
            'is_valid' => $this->is_valid,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'academic_degree' => $this->whenLoaded('academicDegree'),
            'major' => $this->whenLoaded('major'),
            'person' => $this->whenLoaded('person'),
            'images' => $this->whenLoaded('images'),
        ];
    }
}
