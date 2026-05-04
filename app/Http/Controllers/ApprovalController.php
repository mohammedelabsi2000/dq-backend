<?php

// app/Http/Controllers/Api/ApprovalController.php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalRequest;
use Illuminate\Http\Request;

class ApprovalController extends Controller
{
    /**
     * عرض الطلبات المعلقة للمستخدم الحالي حسب مستواه
     */
    public function pending(Request $request)
    {
        $user  = auth()->user();
        $level = $this->resolveUserLevel($user);

        if ($level === null) {
            return response()->json(['data' => []]);
        }

        $requests = ApprovalRequest::with(['approvable', 'requester', 'logs.actor'])
            ->where('status', 'pending')
            ->where('current_level', $level)
            ->get()
            ->filter(fn($req) => $this->userCanActOn($user, $req))
            ->values();

        return response()->json(['data' => $requests]);
    }

    /**
     * اعتماد طلب
     */
    public function approve(Request $request, ApprovalRequest $approvalRequest)
    {
        $user  = auth()->user();
        $level = $this->resolveUserLevel($user);

        if ($level !== $approvalRequest->current_level->value) {
            return response()->json(['message' => 'غير مخوّل للاعتماد في هذه المرحلة'], 403);
        }

        if (!$this->userCanActOn($user, $approvalRequest)) {
            return response()->json(['message' => 'ليس لديك صلاحية على هذا العنصر'], 403);
        }

        if ($approvalRequest->status->value !== 'pending') {
            return response()->json(['message' => 'الطلب ليس في حالة انتظار'], 422);
        }

        $approvalRequest->approve($user, $request->input('notes'));

        $isFullyApproved = $approvalRequest->fresh()->status->value === 'approved';

        return response()->json([
            'message' => $isFullyApproved
                ? 'تم الاعتماد النهائي وأصبح العنصر نشطاً'
                : 'تم الاعتماد وانتقل للمرحلة التالية: ' . $approvalRequest->fresh()->current_level->label(),
        ]);
    }

    /**
     * رفض طلب
     */
    public function reject(Request $request, ApprovalRequest $approvalRequest)
    {
        $user  = auth()->user();
        $level = $this->resolveUserLevel($user);

        if ($level !== $approvalRequest->current_level->value) {
            return response()->json(['message' => 'غير مخوّل للرفض في هذه المرحلة'], 403);
        }

        if (!$this->userCanActOn($user, $approvalRequest)) {
            return response()->json(['message' => 'ليس لديك صلاحية على هذا العنصر'], 403);
        }

        if ($approvalRequest->status->value !== 'pending') {
            return response()->json(['message' => 'الطلب ليس في حالة انتظار'], 422);
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $approvalRequest->reject($user, $validated['reason']);

        return response()->json(['message' => 'تم رفض الطلب']);
    }

    /**
     * إعادة إرسال بعد تعديل البيانات
     */
    public function resubmit(Request $request, $id)
    {
        $modelClass = $request->route()->defaults['model'];

        $record = $modelClass::withPending()->findOrFail($id);

        if (auth()->id() !== $record->approvalRequest?->requested_by) {
            return response()->json(['message' => 'فقط من أضاف العنصر يمكنه إعادة الإرسال'], 403);
        }

        if ($record->approvalRequest?->status->value !== 'rejected') {
            return response()->json(['message' => 'لا يمكن إعادة الإرسال إلا بعد الرفض'], 422);
        }

        // تحديث البيانات إن أُرسلت
        if ($request->hasAny(array_keys($request->all()))) {
            $record->update($request->validated());
        }

        $record->approvalRequest->resubmit();

        return response()->json(['message' => 'تم إعادة الإرسال وسيُراجع من مدير المنطقة']);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    /**
     * تحديد مستوى المستخدم في سير العمل
     */
    private function resolveUserLevel(mixed $user): ?string
    {
        if ($user->isGlobalAdmin()) {
            return 'admin';
        }

        if ($user->getScopeIds('branch')->isNotEmpty()) {
            return 'branch';
        }

        if ($user->getScopeIds('region')->isNotEmpty()) {
            return 'region';
        }

        return null;
    }

    /**
     * التحقق أن المستخدم مخوّل على العنصر المحدد ضمن نطاقه
     */
    private function userCanActOn(mixed $user, ApprovalRequest $approvalRequest): bool
    {
        if ($user->isGlobalAdmin()) {
            return true;
        }

        $approvable = $approvalRequest->approvable;

        if (!$approvable) {
            return false;
        }

        // نستخدم scopeVisibleTo الموجود في كل موديل
        $modelClass = get_class($approvable);

        return $modelClass::withPending()
            ->visibleTo($user)
            ->where('id', $approvable->id)
            ->exists();
    }
}
