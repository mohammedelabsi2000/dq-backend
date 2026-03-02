<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ImageResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'file_name' => $this->file_name,
            'file_url' => asset('storage/' . $this->file_path),
            'mime_type' => $this->mime_type,
            'file_size' => $this->file_size,
            'image_type' => $this->image_type,
            'is_main' => $this->is_main,
            'notes' => $this->notes,
            'created_at' => $this->created_at,
        ];
    }
}
