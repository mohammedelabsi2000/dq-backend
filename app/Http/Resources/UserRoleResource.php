<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserRoleResource extends JsonResource
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
            'id'         => $this->id,
            'name'       => $this->name,
            'scope_id'   => $this->pivot->scope_id,
            'scope_type' => $this->pivot->scope_type,
            'abilities'  => RoleAbilityResource::collection($this->whenLoaded('roleAbilities')),
        ];
    }
}
