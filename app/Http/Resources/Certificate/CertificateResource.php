<?php

namespace App\Http\Resources\Certificate;

use App\Http\Resources\ImageResource;
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
            'certificate_link' => $this->certificate_link,
            'date_graduate' => $this->date_graduate,
            'provider' => $this->provider,
            'certificate_type' => $this->certificate_type,
            'certificate_type_label' => $this->certificate_type_label,
            'academic_qualification_id' => $this->academic_qualification_id,
            'major_id' => $this->major_id,
            'course_name' => $this->course_name,
            'course_type_id' => $this->course_type_id,
            'notes' => $this->notes,
            'academic_qualification' => $this->whenLoaded('academicQualification'),
            'major' => $this->whenLoaded('major'),
            'course_type' => $this->whenLoaded('courseType'),
            'person' => $this->whenLoaded('person'),
            'person_full_name' => $this->person->full_name ?? null,

            'images' => $this->whenLoaded('images', function () {
                return ImageResource::collection($this->images);
            }),
        ];
    }
}
