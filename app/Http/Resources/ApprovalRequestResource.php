<?php

namespace App\Http\Resources;

use App\Models\Halaqa;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\Resources\Json\JsonResource;

class ApprovalRequestResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'status'           => $this->status->value,
            'status_label'     => $this->status->label(),
            'rejection_reason' => $this->rejection_reason,
            'requested_at'     => ($this->submitted_at ?? $this->created_at)?->format('Y-m-d H:i'),
            'decided_at'       => $this->decided_at?->format('Y-m-d H:i'),

            // نوع الطلب (approvable_type مخزّن كـ alias عبر morph map: user/halaqa/student)
            'type' => match ($this->approvable_type) {
                'user'    => 'user',
                'halaqa'  => 'halaqa',
                'student' => 'student',
                default   => $this->approvable_type,
            },

            // الكيان المطلوب اعتماده
            'approvable' => $this->when(
                $this->relationLoaded('approvable'),
                fn() => match (true) {
                    $this->approvable instanceof User    => new UserResource($this->approvable),
                    $this->approvable instanceof Halaqa   => $this->formatHalaqa($this->approvable),
                    $this->approvable instanceof Student  => $this->formatStudent($this->approvable),
                    default                               => null,
                }
            ),

            // مقدم الطلب
            'created_by' => $this->when(
                $this->relationLoaded('requester'),
                fn() => $this->requester ? $this->formatRequester($this->requester) : null,
            ),
        ];
    }


    private function formatRequester(User $requester): array
    {
        return [
            'id' => $requester->id,
            // 'full_name' => trim(preg_replace('/\s+/', ' ', $requester->full_name)),
            'full_name' => $requester->full_name,
            'role_name' => $requester->relationLoaded('roles')
                ? $requester->roles->pluck('name')->implode('، ')
                : null,
            'user_scopes' => ScopeResource::collection(
                $requester->scopes()->whereNull('to_date')->get()
                    ->flatMap(fn($scope) => $scope->toHierarchyCollection())
            ),
        ];
    }

    private function formatHalaqa(Halaqa $halaqa): array
    {
        return [
            'id'        => $halaqa->id,
            'full_name' => $halaqa->name,
        ];
    }

    private function formatStudent(Student $student): array
    {
        return [
            'id'        => $student->id,
            'full_name' => $student->full_name,
            'identity'  => $student->identity,
        ];
    }
}
