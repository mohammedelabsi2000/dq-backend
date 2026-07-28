<?php
// app/Http/Controllers/ApprovalController.php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ApprovalRequestResource;
use App\Models\ApprovalRequest;
use Illuminate\Http\Request;
use App\Models\Halaqa;
use App\Models\Student;
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

        // approvable_type مخزّن في قاعدة البيانات وفق morph map كـ alias (user/halaqa/student)
        // وليس اسم الكلاس الكامل، لذلك يجب الفلترة بنفس الـ alias
        $type = match ($request->input('type')) {
            'users', 'user'       => 'user',
            'halaqas', 'halaqa'   => 'halaqa',
            'students', 'student' => 'student',
            default               => null,
        };

        // أنواع الطلبات التي يملك المستخدم صلاحية الاعتماد أو الرفض عليها
        // مدير الدائرة (Global Admin) يرى كل الأنواع دون قيد
        $allowedTypes = $user->isGlobalAdmin()
            ? ['user', 'halaqa', 'student']
            : collect(['users' => 'user', 'halaqas' => 'halaqa', 'students' => 'student'])
                ->filter(fn($alias, $prefix) => $user->hasPermissionTo("$prefix.approve") || $user->hasPermissionTo("$prefix.reject"))
                ->values()
                ->all();

        $query = ApprovalRequest::visibleTo($user, $type)
            ->where(
                fn($q) =>
                $q->whereIn('approvable_type', $allowedTypes)
                    ->orWhere('requested_by', $user->id)
            )
            ->when(
                $request->filled('status'),
                fn($q) =>
                $q->where('status', $request->input('status'))
            )
            ->when(
                $request->filled('from_date'),
                fn($q) =>
                $q->whereDate('created_at', '>=', $request->input('from_date'))
            )
            ->when(
                $request->filled('to_date'),
                fn($q) =>
                $q->whereDate('created_at', '<=', $request->input('to_date'))
            )
            ->with([
                'approvable' => fn($morphTo) => $morphTo->morphWith([User::class => ['roles']]),
                'requester.roles',
            ]);

        $q = $this->applyFilters($query, [
            'orderColumn' => 'created_at',
            'orderBy' => 'desc',
        ]);

        $requests = $q['query']->get();

        return $this->successWithPagination(
            ApprovalRequestResource::collection($requests),
            ['total' => $q['count'], 'skip' => $q['skip'], 'limit' => $q['limit']],
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
            return $this->error('ليس لديك صلاحية للوصول لهذا الطلب.', 422);
        }

        // ✅ التحقق أن المستوى الحالي يخصه
        if (!$approvalRequest->canActOn($user)) {
            return $this->error('هذا الطلب ليس في مرحلتك حالياً.', 422);
        }

        try {
            $approvalRequest->approve($user);
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
