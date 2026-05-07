<?php

namespace App\Http\Resources;

use App\Models\Halaqa;
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
            'current_level'    => $this->current_level->value,
            'current_level_label' => $this->current_level->label(),
            'rejection_reason' => $this->rejection_reason,
            'requested_at'     => $this->created_at?->format('Y-m-d H:i'),

            // نوع الطلب
            'type' => match ($this->approvable_type) {
                User::class   => 'user',
                Halaqa::class => 'halaqa',
                default       => $this->approvable_type,
            },

            // الكيان المطلوب اعتماده
            'approvable' => $this->when(
                $this->relationLoaded('approvable'),
                fn() => match (true) {
                    $this->approvable instanceof User   => new UserResource($this->approvable),
                    $this->approvable instanceof Halaqa => $this->formatHalaqa($this->approvable),
                    default                             => null,
                }
            ),

            // مقدم الطلب
            'created_by' => $this->when(
                $this->relationLoaded('requester'),
                fn() => $this->requester ? new UserResource($this->requester) : null,
            ),

            // سجل الإجراءات
            'logs' => $this->when(
                $this->relationLoaded('logs'),
                fn() => $this->logs->map(fn($log) => [
                    'level'      => \App\Enums\ApprovalLevel::from($log->level)->label(),
                    'action'     => $log->action === 'approved' ? 'موافقة' : 'رفض',
                    'acted_by'   => $log->actor?->full_name ?? $log->actor?->name,
                    'notes'      => $log->notes,
                    'created_at' => $log->created_at?->format('Y-m-d H:i'),
                ])
            ),
        ];
    }


    private function formatHalaqa(Halaqa $halaqa): array
    {
        return [
            'id'             => $halaqa->id,
            'name'           => $halaqa->name,
            'reference_type' => $halaqa->reference_type?->value,
            'reference_id'   => $halaqa->reference_id,
        ];
    }
}
