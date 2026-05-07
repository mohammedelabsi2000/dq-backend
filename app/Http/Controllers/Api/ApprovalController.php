<?php
// app/Http/Controllers/ApprovalController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Illuminate\Http\Request;
use App\Models\Halaqa;
use App\Models\User;
use App\Http\Traits\ApiResponser;

class ApprovalController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the approval requests.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', ApprovalRequest::class);
        $user = $request->user();

        $type = match ($request->input('type')) {
            'users'   => User::class,
            'halaqas' => Halaqa::class,
            default   => null,
        };

        // $request = ApprovalRequest::visibleTo($user)->

        $requests = ApprovalRequest::visibleTo($user, $type)
            ->when(
                $request->filled('status'),
                fn($q) =>
                $q->where('status', $request->input('status'))
            )
            ->with(['approvable.roles', 'requester.roles', 'logs.actor'])
            ->latest()
            ->paginate(20);

        return $this->successWithPagination(
            ApprovalRequestResource::collection($requests->items()),
            $this->paginate($requests),
            'قائمة طلبات الموافقة'
        );
    }

    public function approve(Request $request, ApprovalRequest $approvalRequest)
    {
        $this->authorize('approve', $approvalRequest);
        $user = $request->user();

        // ✅ التحقق أن الطلب في نطاقه
        $isVisible = ApprovalRequest::visibleTo($user)
            ->where('id', $approvalRequest->id)
            ->exists();

        if (!$isVisible) {
            return $this->error('ليس لديك صلاحية للوصول لهذا الطلب.', 403);
        }

        // ✅ التحقق أن المستوى الحالي يخصه
        if (!$approvalRequest->canActOn($user)) {
            return $this->error('هذا الطلب ليس في مرحلتك حالياً.', 403);
        }

        try {
            $approvalRequest->approve($user, $request->input('notes'));
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        // return response()->json(['message' => 'تمت الموافقة بنجاح.']);
        return $this->success(
            null,
            'تمت الموافقة بنجاح.'
        );
    }

    public function reject(Request $request, ApprovalRequest $approvalRequest)
    {
        $this->authorize('reject', $approvalRequest);
        $user = $request->user();

        $isVisible = ApprovalRequest::visibleTo($user)
            ->where('id', $approvalRequest->id)
            ->exists();

        if (!$isVisible) {
            return $this->error('ليس لديك صلاحية للوصول لهذا الطلب.', 422);
        }

        // ✅ التحقق أن المستوى الحالي يخصه
        if (!$approvalRequest->canActOn($user)) {
            return $this->error('هذا الطلب ليس في مرحلتك حالياً.', 422);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ]);

        try {
            $approvalRequest->reject($user, $request->input('rejection_reason'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(null, 'تم الرفض بنجاح');
    }

    public function resubmit(Request $request, ApprovalRequest $approvalRequest)
    {
        $this->authorize('resubmit', $approvalRequest);
        $user = $request->user();

        // فقط مقدم الطلب يمكنه إعادة الإرسال
        if ($approvalRequest->requested_by !== $user->id) {
            return $this->error(
                'ليس لديك صلاحية لإعادة إرسال هذا الطلب.',
                403
            );
        }

        try {
            $approvalRequest->resubmit();
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            null,
            'تمت إعادة إرسال الطلب بنجاح'
        );
    }
}
