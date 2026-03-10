<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleAbilityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        // $ability = config('abilities')[$this->ability] ?? [];

        // return [
        //     'id'      => $this->id,
        //     'ability' => $this->ability,
        //     'label'   => [
        //         'en' => $ability['en'] ?? $this->ability,
        //         'ar' => $ability['ar'] ?? $this->ability,
        //     ],
        //     'type'    => $this->type,
        // ];

        // $ability = collect(config('abilities'))
        //     ->flatten(1)
        //     ->firstWhere('ability', $this->ability);

        // return [
        //     'id'      => $this->id,
        //     'ability' => $this->ability,
        //     'en'      => $ability['en'] ?? $this->ability,
        //     'ar'      => $ability['ar'] ?? $this->ability,
        //     'type'    => $this->type,
        // ];

        $ability = collect(config('abilities'))
            ->flatten(1)
            ->firstWhere('ability', $this->ability);

        return [
            'id'      => $this->id,
            'ability' => $this->ability,
            'label_en'      => $ability['label_en'] ?? $this->ability,
            'label_ar'      => $ability['label_ar'] ?? $this->ability,
            'type'    => $this->type,
        ];
    }
}
