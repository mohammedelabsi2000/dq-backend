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
            return $this->error($approvalRequest->cannotActReason() ?? 'هذا الطلب ليس في مرحلتك حالياً.', 422);
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
            return $this->error($approvalRequest->cannotActReason() ?? 'هذا الطلب ليس في مرحلتك حالياً.', 422);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500',
        ], [
            'rejection_reason.required' => 'سبب الرفض مطلوب.',
            'rejection_reason.string'   => 'سبب الرفض يجب أن يكون نصاً.',
            'rejection_reason.max'      => 'سبب الرفض يجب ألا يتجاوز 500 حرف.',
        ]);

        try {
            $approvalRequest->reject($user, $request->input('rejection_reason'));
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(null, 'تم الرفض بنجاح');
    }

    /**
     * الموافقة على أكثر من طلب اعتماد في إجراء واحد.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkApprove(Request $request)
    {
        $validated = $request->validate([
            'ids'   => 'required|array|min:1',
            'ids.*' => 'integer|distinct|exists:approval_requests,id',
        ], $this->bulkIdsMessages());

        $this->authorize('viewAny', ApprovalRequest::class);

        $user = $request->user();
        $approvalRequests = ApprovalRequest::whereIn('id', $validated['ids'])->get();

        [$approved, $failed] = $this->processBulkAction(
            $approvalRequests,
            $user,
            'approve',
            fn(ApprovalRequest $approvalRequest) => $approvalRequest->approve($user)
        );

        return $this->respondToBulkAction($approved, $failed, 'approved', [
            'all'     => 'تمت الموافقة على جميع الطلبات المحددة بنجاح.',
            'partial' => 'تمت الموافقة على بعض الطلبات، وفشلت أخرى.',
            'none'    => 'تعذّر تنفيذ الموافقة على أي من الطلبات المحددة.',
        ]);
    }

    /**
     * رفض أكثر من طلب اعتماد في إجراء واحد.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function bulkReject(Request $request)
    {
        $validated = $request->validate([
            'ids'               => 'required|array|min:1',
            'ids.*'             => 'integer|distinct|exists:approval_requests,id',
            'rejection_reason'  => 'required|string|max:500',
        ], $this->bulkIdsMessages() + [
            'rejection_reason.required' => 'سبب الرفض مطلوب.',
            'rejection_reason.string'   => 'سبب الرفض يجب أن يكون نصاً.',
            'rejection_reason.max'      => 'سبب الرفض يجب ألا يتجاوز 500 حرف.',
        ]);

        $this->authorize('viewAny', ApprovalRequest::class);

        $user = $request->user();
        $approvalRequests = ApprovalRequest::whereIn('id', $validated['ids'])->get();

        [$rejected, $failed] = $this->processBulkAction(
            $approvalRequests,
            $user,
            'reject',
            fn(ApprovalRequest $approvalRequest) => $approvalRequest->reject($user, $validated['rejection_reason'])
        );

        return $this->respondToBulkAction($rejected, $failed, 'rejected', [
            'all'     => 'تم رفض جميع الطلبات المحددة بنجاح.',
            'partial' => 'تم رفض بعض الطلبات، وفشلت أخرى.',
            'none'    => 'تعذّر تنفيذ الرفض على أي من الطلبات المحددة.',
        ]);
    }

    /**
     * رسائل عربية موحدة للتحقق من حقل ids المشترك بين إجراءات الاعتماد الجماعية.
     */
    private function bulkIdsMessages(): array
    {
        return [
            'ids.required'   => 'يجب تحديد طلب واحد على الأقل.',
            'ids.array'      => 'يجب أن تكون الطلبات المحددة في صورة قائمة.',
            'ids.min'        => 'يجب تحديد طلب واحد على الأقل.',
            'ids.*.integer'  => 'معرف الطلب غير صالح.',
            'ids.*.distinct' => 'لا يمكن تحديد نفس الطلب أكثر من مرة.',
            'ids.*.exists'   => 'أحد الطلبات المحددة غير موجود.',
        ];
    }

    /**
     * بناء استجابة إجراء جماعي بناءً على عدد العناصر الناجحة والفاشلة:
     * كلها نجحت → نجاح كامل، بعضها نجح → نجاح جزئي، كلها فشلت → خطأ.
     *
     * @param array<int> $succeededIds
     * @param array<int, array{id:int, message:string}> $failed
     * @param string $succeededKey مفتاح مصفوفة العناصر الناجحة في الاستجابة (approved/rejected)
     * @param array{all:string, partial:string, none:string} $messages
     */
    private function respondToBulkAction(array $succeededIds, array $failed, string $succeededKey, array $messages)
    {
        if (empty($succeededIds)) {
            return $this->error($messages['none'], 422, ['failed' => $failed]);
        }

        return $this->success(
            [$succeededKey => $succeededIds, 'failed' => $failed],
            empty($failed) ? $messages['all'] : $messages['partial']
        );
    }

    /**
     * تنفيذ إجراء (موافقة/رفض) على مجموعة طلبات، مع تجميع النتائج الناجحة والفاشلة
     * دون أن يوقف فشل عنصر واحد بقية الدفعة.
     *
     * @param \Illuminate\Support\Collection<int, ApprovalRequest> $approvalRequests
     * @param User $user
     * @param string $ability
     * @param callable $action
     * @return array{0: array<int>, 1: array<int, array{id:int, message:string}>}
     */
    private function processBulkAction($approvalRequests, User $user, string $ability, callable $action): array
    {
        $succeeded = [];
        $failed = [];

        foreach ($approvalRequests as $approvalRequest) {
            try {
                if (!$user->can($ability, $approvalRequest)) {
                    throw new \Exception('ليس لديك صلاحية لتنفيذ هذا الإجراء على هذا الطلب.');
                }

                $isVisible = ApprovalRequest::visibleTo($user)
                    ->where('id', $approvalRequest->id)
                    ->exists();

                if (!$isVisible) {
                    throw new \Exception('ليس لديك صلاحية للوصول لهذا الطلب.');
                }

                if (!$approvalRequest->canActOn($user)) {
                    throw new \Exception($approvalRequest->cannotActReason() ?? 'هذا الطلب ليس في مرحلتك حالياً.');
                }

                $action($approvalRequest);
                $succeeded[] = $approvalRequest->id;
            } catch (\Exception $e) {
                $failed[] = ['id' => $approvalRequest->id, 'message' => $e->getMessage()];
            }
        }

        return [$succeeded, $failed];
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
