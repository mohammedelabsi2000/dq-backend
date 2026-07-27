<?php

namespace App\Policies;

use App\Enums\ApprovalStatus;
use App\Models\ApprovalRequest;
use App\Models\Halaqa;
use App\Models\Student;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApprovalPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('approvals.show');
    }

    public function view(User $user, ApprovalRequest $approvalRequest): bool
    {
        if (!$user->hasPermissionTo('approvals.show')) {
            return false;
        }

        if ($approvalRequest->requested_by === $user->id) {
            return true;
        }

        return ApprovalRequest::visibleTo($user)
            ->where('id', $approvalRequest->id)
            ->exists();
    }

    public function approve(User $user, ApprovalRequest $approvalRequest): bool
    {
        if ($approvalRequest->requested_by === $user->id) {
            return false;
        }

        return $user->hasPermissionTo($this->permissionFor($approvalRequest, 'approve'));
    }

    public function reject(User $user, ApprovalRequest $approvalRequest): bool
    {
        if ($approvalRequest->requested_by === $user->id) {
            return false;
        }

        return $user->hasPermissionTo($this->permissionFor($approvalRequest, 'reject'));
    }

    public function resubmit(User $user, ApprovalRequest $approvalRequest): bool
    {
        if (!$user->hasPermissionTo('approvals.resubmit')) {
            return false;
        }

        return $approvalRequest->requested_by === $user->id
            && $approvalRequest->status === ApprovalStatus::Rejected;
    }

    private function permissionFor(ApprovalRequest $approvalRequest, string $action): string
    {
        // approvable_type مخزّن في قاعدة البيانات وفق morph map كـ alias (user/halaqa/student)
        // وليس اسم الكلاس الكامل
        return match ($approvalRequest->approvable_type) {
            'user'    => "users.$action",
            'halaqa'  => "halaqas.$action",
            'student' => "students.$action",
            default   => "approvals.$action",
        };
    }
}
