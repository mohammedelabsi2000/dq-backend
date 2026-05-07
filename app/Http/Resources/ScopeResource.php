<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ScopeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        $name = null;

        switch ($this->scope_type) {
            case 'branch':
                $branch = \App\Models\Branch::find($this->scope_id);
                $name = $branch?->name;
                break;

            case 'region':
                $region = \App\Models\Region::find($this->scope_id);
                $name = $region?->name;
                break;

            case 'center':
                $center = \App\Models\Center::find($this->scope_id);
                $name = $center?->name;
                break;

            case 'halaqa':
                $halaqa = \App\Models\Halaqa::find($this->scope_id);
                $name = $halaqa?->name;
                break;
        }

        return [
            'type' => $this->scope_type,
            'id'   => $this->scope_id,
            'name' => $name,
        ];
    }
}
