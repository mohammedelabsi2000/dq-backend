<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomJuzResource extends JsonResource
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
            "id"=> $this->id,
            "name"=> $this->name,
            "sort_order"=> $this->sort_order,
            
            "start_surah"=> $this->start_surah,
            "start_surah_id"=> $this->start_surah_id,
            "start_aya"=> $this->start_aya,
            
            "end_surah"=> $this->end_surah,
            "end_surah_id"=> $this->end_surah_id,
            "end_aya"=> $this->end_aya,
        ];
    }
}
