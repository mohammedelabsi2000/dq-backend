<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
        'fName' => $this->fName,
        'sName' => $this->sName,
        'thName' => $this->thName,
        'family' => $this->family,
        'full_name' => $this->full_name,
        'dob' => $this->dob,
        'gender' => $this->gender,
        'numChildren' => $this->numChildren,
        'identity' => $this->identity,
        'phone' => $this->phone,
        'whatsapp' => $this->whatsapp,
        'jobname' => $this->jobname,
        'job_place' => $this->job_place,
        'job_salary' => $this->job_salary,

        // العلاقات
        'mosque' => new MosqueResource($this->whenLoaded('mosque')),
        'marital_status' => new ConstantResource($this->whenLoaded('maritalStatus')),
        'prefix_name' => new ConstantResource($this->whenLoaded('prefixName')),

        'location' => $this->location,

        // لو عندك صورة بعدين
        // 'image' => new ImageResource($this->whenLoaded('image')),
    ];
}
}
