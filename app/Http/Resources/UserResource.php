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
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'fName' => $this->fName,
            'sName' => $this->sName,
            'thName' => $this->thName,
            'family' => $this->family,
            'full_name' => trim(preg_replace('/\s+/', ' ', $this->full_name)),
            'dob' => $this->dob,
            'gender' => $this->gender?->label(),
            'genderText' => $this->gender_text,
            'numChildren' => $this->numChildren,
            'identity' => $this->identity,
            'phone' => $this->phone,
            'whatsapp' => $this->whatsapp,
            'email' => $this->email,
            'jobname' => $this->jobname,
            'job_place' => $this->job_place,
            'job_salary' => $this->job_salary,

            // العلاقات
            'mosque' => new MosqueResource($this->mosque),
            'marital_status' => new ConstantResource($this->whenLoaded('maritalStatus')),
            'prefix_name' => new ConstantResource($this->whenLoaded('prefix')),
            'roles' => UserRoleResource::collection($this->whenLoaded('roles')),
            'abilities' => $this->when(
                $this->relationLoaded('roles'),
                fn() => $this->roles
                    ->flatMap(fn($role) => $role->permissions)
                    ->unique('ability')
                    ->values()
                    ->map(fn($ability) => [
                        'ability' => $ability->ability,
                        'type' => $ability->type,
                    ])
            ),

            'user_scopes' => ScopeResource::collection(
                $this->scopes()->whereNull('to_date')->get()
                    ->flatMap(fn($scope) => $scope->toHierarchyCollection())
            ),


            'location' => $this->location,
            'approval' => [
                'is_approved'      => $this->is_approved,
                'status'           => $this->approvalRequest?->status?->label(),
                'current_level'    => $this->approvalRequest?->current_level?->label(),
                'rejection_reason' => $this->approvalRequest?->rejection_reason,
            ],

        ];
    }
}
