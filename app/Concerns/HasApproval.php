<?php
// app/Concerns/HasApproval.php

namespace App\Concerns;

use App\Enums\ApprovalStatus;
use App\Models\ApprovalRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

trait HasApproval
{
    public function approvalRequest()
    {
        return $this->morphOne(ApprovalRequest::class, 'approvable');
    }

    public function submitForApproval(User $requester): ?ApprovalRequest
    {
        if ($this->approvalRequest()->exists()) {
            throw new \Exception('يوجد طلب اعتماد مسبق لهذا العنصر.');
        }

        // المدير العام أو تفعيل الاعتماد التلقائي لهذا النوع → اعتماد فوري بدون طلب
        if ($requester->isGlobalAdmin() || $this->autoApprovalEnabled()) {
            $this->update($this->approvedAttributes());
            return null;
        }

        return $this->approvalRequest()->create([
            'status'       => ApprovalStatus::Pending,
            'requested_by' => $requester->id,
            'submitted_at' => now(),
        ]);
    }

    /**
     * هل الاعتماد التلقائي مفعّل لهذا النوع؟ افتراضياً معطّل.
     * النماذج التي تدعم الاعتماد التلقائي (الحلقات والطلاب) تُجاوز هذه الدالة.
     */
    public function autoApprovalEnabled(): bool
    {
        return false;
    }

    /**
     * القيم التي تُحدَّث على النموذج عند اعتماده. النماذج التي لها سلوك إضافي
     * (مثل تفعيل تسجيل الدخول للمستخدم) يمكنها تجاوز هذه الدالة.
     */
    public function approvedAttributes(): array
    {
        return ['is_approved' => true];
    }

    public function isPending(): bool
    {
        return $this->approvalRequest?->status === ApprovalStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->is_approved === true;
    }

    public function isRejected(): bool
    {
        return $this->approvalRequest?->status === ApprovalStatus::Rejected;
    }

    public static function withPending(): Builder
    {
        return static::withoutGlobalScope('approved');
    }
}
