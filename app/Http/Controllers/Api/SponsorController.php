<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Sponsor\StoreSponsorRequest;
use App\Http\Requests\Sponsor\UpdateSponsorRequest;
use App\Http\Resources\HalaqaSponsorshipResource;
use App\Http\Resources\SponsorResource;
use App\Models\Sponsor;
use Illuminate\Http\Request;

class SponsorController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Sponsor::class);

        $query = Sponsor::query();

        if ($request->filled('is_active')) {
            $query->where('is_active', $request->boolean('is_active'));
        }

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name', 'project_number', 'follow_up_entity'],
            'orderColumn' => 'created_at',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        $sponsors = $query->with('studentType')->withCount('activeHalaqaSponsorships')->get();

        return $this->successWithPagination(
            SponsorResource::collection($sponsors),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  StoreSponsorRequest  $request
     */
    public function store(StoreSponsorRequest $request)
    {
        $sponsor = Sponsor::create($request->validated());

        return $this->success(
            new SponsorResource($sponsor->load('studentType')),
            'تم إنشاء الكفيل بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  Sponsor  $sponsor
     */
    public function show(Sponsor $sponsor)
    {
        $this->authorize('view', $sponsor);

        $sponsor->load(['studentType', 'activeHalaqaSponsorships', 'attachments']);

        return $this->success(
            new SponsorResource($sponsor),
            'بيانات الكفيل'
        );
    }

    /**
     * الحلقات التي يكفلها هذا الكافل (الفعّالة افتراضياً، أو كل السجل عبر with_history=1).
     *
     * @param  Request  $request
     * @param  Sponsor  $sponsor
     */
    public function halaqas(Request $request, Sponsor $sponsor)
    {
        $this->authorize('view', $sponsor);

        $query = $sponsor->halaqaSponsorships();

        if (!$request->boolean('with_history')) {
            $query->whereNull('to_date');
        }

        $sponsorships = $query
            ->with(['halaqa.type', 'halaqa.reference'])
            ->orderByDesc('from_date')
            ->get();

        return $this->success(
            HalaqaSponsorshipResource::collection($sponsorships),
            'الحلقات المكفولة'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateSponsorRequest  $request
     * @param  Sponsor  $sponsor
     */
    public function update(UpdateSponsorRequest $request, Sponsor $sponsor)
    {
        $sponsor->update($request->validated());

        return $this->success(
            new SponsorResource($sponsor->load('studentType')),
            'تم تحديث بيانات الكفيل بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Sponsor  $sponsor
     */
    public function destroy(Sponsor $sponsor)
    {
        $this->authorize('delete', $sponsor);

        if ($sponsor->activeHalaqaSponsorships()->exists()) {
            return $this->error(
                'لا يمكن حذف الكفيل لأنه مرتبط بحلقات فعّالة',
                400
            );
        }

        $sponsor->delete();

        return $this->success(
            null,
            'تم حذف الكفيل بنجاح'
        );
    }
}
