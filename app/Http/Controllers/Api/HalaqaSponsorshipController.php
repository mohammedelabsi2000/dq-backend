<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HalaqaSponsorship\StopHalaqaSponsorshipRequest;
use App\Http\Requests\HalaqaSponsorship\StoreHalaqaSponsorshipRequest;
use App\Http\Resources\HalaqaSponsorshipResource;
use App\Models\Constant;
use App\Models\Halaqa;
use App\Models\HalaqaSponsorship;
use App\Models\HalaqaStatus;
use App\Models\Sponsor;
use Illuminate\Support\Facades\DB;

class HalaqaSponsorshipController extends Controller
{
    /**
     * قائمة كل الكفالات (الفعّالة والمتوقفة) لحلقة معيّنة.
     */
    public function index(Halaqa $halaqa)
    {
        $this->authorize('update', $halaqa);

        $query = $halaqa->sponsorships();

        $q = $this->applyFilters($query, [
            'orderColumn' => 'from_date',
            'orderBy' => 'desc',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        $sponsorships = $query->with(['sponsor.studentType', 'halaqa'])->get();

        return $this->successWithPagination(
            HalaqaSponsorshipResource::collection($sponsorships),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'كفالات الحلقة'
        );
    }

    /**
     * الكفلاء المؤهلون لإضافتهم لهذه الحلقة: فعّالون ولديهم سعة متبقية من جنس الحلقة.
     */
    public function eligibleSponsors(Halaqa $halaqa)
    {
        $this->authorize('update', $halaqa);

        $branchId = $halaqa->branchId();

        $sponsors = Sponsor::isActive()
            ->with('studentType')
            ->get()
            ->filter(fn (Sponsor $sponsor) => $sponsor->remainingCapacityForBranch($branchId, $halaqa->gender) > 0)
            ->values();

        return $this->success(
            \App\Http\Resources\SponsorResource::collection($sponsors),
            'الكفلاء المؤهلون لهذه الحلقة'
        );
    }

    /**
     * إضافة كفالة (كفيل) لحلقة. يمكن أن يكون للحلقة أكثر من كفيل فعّال في نفس الوقت.
     */
    public function store(StoreHalaqaSponsorshipRequest $request, Halaqa $halaqa)
    {
        $sponsor = Sponsor::findOrFail($request->validated('sponsor_id'));

        if (!$sponsor->is_active) {
            return $this->error('الكفيل غير فعّال', 422);
        }

        $alreadyLinked = HalaqaSponsorship::where('halaqa_id', $halaqa->id)
            ->where('sponsor_id', $sponsor->id)
            ->whereNull('to_date')
            ->exists();

        if ($alreadyLinked) {
            return $this->error('هذا الكفيل مرتبط بالفعل بهذه الحلقة', 422);
        }

        if ($sponsor->remainingCapacityForBranch($halaqa->branchId(), $halaqa->gender) <= 0) {
            return $this->error('لا يوجد سعة متبقية لدى هذا الكفيل لهذا الجنس من الحلقات ضمن فرع هذه الحلقة', 422);
        }

        $sponsorship = null;

        DB::transaction(function () use ($request, $halaqa, $sponsor, &$sponsorship) {
            $wasSponsored = $halaqa->activeSponsorships()->exists();

            $sponsorship = HalaqaSponsorship::create([
                'halaqa_id' => $halaqa->id,
                'sponsor_id' => $sponsor->id,
                'from_date' => $request->input('from_date', now()->toDateString()),
                'notes' => $request->input('notes'),
            ]);

            if (!$wasSponsored) {
                $this->syncHalaqaStatus($halaqa, true, $sponsor->name);
            }
        });

        return $this->success(
            new HalaqaSponsorshipResource($sponsorship->load(['sponsor.studentType', 'halaqa'])),
            'تمت إضافة الكفالة للحلقة بنجاح',
            201
        );
    }

    /**
     * إيقاف/إلغاء كفالة على حلقة. إذا كانت آخر كفالة فعّالة، تتحول الحلقة إلى غير مكفولة.
     */
    public function stop(StopHalaqaSponsorshipRequest $request, Halaqa $halaqa, HalaqaSponsorship $halaqaSponsorship)
    {
        if ($halaqaSponsorship->halaqa_id !== $halaqa->id) {
            return $this->error('هذه الكفالة لا تخص هذه الحلقة', 404);
        }

        if (!is_null($halaqaSponsorship->to_date)) {
            return $this->error('هذه الكفالة متوقفة بالفعل', 422);
        }

        DB::transaction(function () use ($request, $halaqa, $halaqaSponsorship) {
            $halaqaSponsorship->to_date = $request->input('to_date', now()->toDateString());
            $halaqaSponsorship->stop_reason = $request->input('stop_reason');
            $halaqaSponsorship->save();

            $stillSponsored = $halaqa->activeSponsorships()->exists();

            if (!$stillSponsored) {
                $this->syncHalaqaStatus($halaqa, false, null);
            }
        });

        return $this->success(
            new HalaqaSponsorshipResource($halaqaSponsorship->load(['sponsor.studentType', 'halaqa'])),
            'تم إيقاف الكفالة بنجاح'
        );
    }

    /**
     * يُنشئ صفًا جديدًا في سجل حالات الحلقة (HalaqaStatus) فقط عند انتقال حالة الكفالة
     * الإجمالية للحلقة (مكفولة/غير مكفولة)، وليس عند كل إضافة/إيقاف كفيل بشكل فردي.
     */
    private function syncHalaqaStatus(Halaqa $halaqa, bool $isSponsored, ?string $sponsorName): void
    {
        $constantName = $isSponsored ? 'مكفولة' : 'غير مكفولة';

        $sponsorshipType = Constant::where('name', $constantName)
            ->whereHas('type', fn ($q) => $q->where('name', 'sponsorship_type'))
            ->first();

        $lastStatus = $halaqa->statuses()->latest('id')->first();
        if ($lastStatus && is_null($lastStatus->to_date)) {
            $lastStatus->to_date = now()->toDateString();
            $lastStatus->save();
        }

        HalaqaStatus::create([
            'halaqa_id' => $halaqa->id,
            'sponsorship_type_id' => $sponsorshipType?->id,
            'sponsor_entity' => $sponsorName,
            'from_date' => now()->toDateString(),
            'to_date' => null,
            'notes' => $isSponsored ? 'تمت إضافة كفالة: '.$sponsorName : 'تم إيقاف آخر كفالة فعّالة على الحلقة',
        ]);
    }
}
