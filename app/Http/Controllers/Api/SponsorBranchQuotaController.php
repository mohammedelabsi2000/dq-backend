<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\SponsorBranchQuota\StoreSponsorBranchQuotaRequest;
use App\Http\Requests\SponsorBranchQuota\UpdateSponsorBranchQuotaRequest;
use App\Http\Resources\SponsorBranchQuotaResource;
use App\Models\Sponsor;
use App\Models\SponsorBranchQuota;

class SponsorBranchQuotaController extends Controller
{
    /**
     * قائمة الحصص الموزّعة على الأفرع لكفيل معيّن.
     */
    public function index(Sponsor $sponsor)
    {
        $this->authorize('view', $sponsor);

        $query = $sponsor->branchQuotas();

        $q = $this->applyFilters($query, [
            'orderColumn' => 'created_at',
            'orderBy' => 'desc',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        $quotas = $query->with('branch')->get()
            ->each->setRelation('sponsor', $sponsor);

        return $this->successWithPagination(
            SponsorBranchQuotaResource::collection($quotas),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'حصص الكفيل على الأفرع'
        );
    }

    /**
     * تخصيص حصة (عدد حلقات) لفرع معيّن من إجمالي حصص الكفيل لجنس معيّن.
     */
    public function store(StoreSponsorBranchQuotaRequest $request, Sponsor $sponsor)
    {
        $data = $request->validated();

        // قد يكون هناك صف محذوف (Soft Delete) لنفس (sponsor_id, branch_id, gender)؛ القيد الفريد بقاعدة
        // البيانات لا يتجاهل السجلات المحذوفة، فنعيد إحياءه بدل محاولة إدراج صف جديد يتعارض معه.
        $trashedQuota = $sponsor->branchQuotas()
            ->withTrashed()
            ->where('branch_id', $data['branch_id'])
            ->where('gender', $data['gender'])
            ->first();

        if ($trashedQuota && $trashedQuota->trashed()) {
            $trashedQuota->restore();
            $trashedQuota->update($data);
            $quota = $trashedQuota;
        } else {
            $quota = $sponsor->branchQuotas()->create($data);
        }

        return $this->success(
            new SponsorBranchQuotaResource($quota->load('branch')->setRelation('sponsor', $sponsor)),
            'تم تخصيص الحصة للفرع بنجاح',
            201
        );
    }

    /**
     * تعديل حصة فرع (زيادة/تخفيض عدد الحلقات المخصصة له).
     */
    public function update(UpdateSponsorBranchQuotaRequest $request, Sponsor $sponsor, SponsorBranchQuota $sponsorBranchQuota)
    {
        if ($sponsorBranchQuota->sponsor_id !== $sponsor->id) {
            return $this->error('هذه الحصة لا تخص هذا الكفيل', 404);
        }

        $sponsorBranchQuota->update($request->validated());

        return $this->success(
            new SponsorBranchQuotaResource($sponsorBranchQuota->load('branch')->setRelation('sponsor', $sponsor)),
            'تم تعديل الحصة بنجاح'
        );
    }

    /**
     * حذف تخصيص حصة فرع (تعود حلقاته إن لم تكن حصته مستخدمة بالكامل، وإلا يُمنع الحذف).
     */
    public function destroy(Sponsor $sponsor, SponsorBranchQuota $sponsorBranchQuota)
    {
        $this->authorize('update', $sponsor);

        if ($sponsorBranchQuota->sponsor_id !== $sponsor->id) {
            return $this->error('هذه الحصة لا تخص هذا الكفيل', 404);
        }

        $assignedInBranch = $sponsor->activeHalaqaSponsorships()
            ->whereHas('halaqa', fn ($q) => $q->where('gender', $sponsorBranchQuota->gender->value)
                ->inBranch($sponsorBranchQuota->branch_id))
            ->count();

        if ($assignedInBranch > 0) {
            return $this->error('لا يمكن حذف هذه الحصة لأنه يوجد حلقات مرتبطة فعلياً ضمن هذا الفرع', 422);
        }

        $sponsorBranchQuota->delete();

        return $this->success(null, 'تم حذف الحصة بنجاح');
    }
}
