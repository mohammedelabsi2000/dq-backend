<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Region\StoreRegionRequest;
use App\Http\Requests\Region\UpdateRegionRequest;
use App\Http\Resources\RegionResource;
use App\Models\Branch;
use App\Models\Region;
use Illuminate\Http\Request;

class RegionController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Region::class);
        $query = Region::query()->visibleTo(auth()->user());

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        // فلترة حسب الفرع
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->integer('branch_id'));
        }

        if ($request->boolean('with_branch')) {
            $query->with('branch');
        }

        $regions = $query->withCount('mosques')->get();

        return $this->successWithPagination(
            RegionResource::collection($regions),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreRegionRequest $request)
    {

        $region = Region::create($request->validated());

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_branch')) {
            $region->load('branch');
        }

        return $this->success(
            new RegionResource($region),
            'تم إنشاء المنطقة بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Region $region
     */
    public function show(Request $request, Region $region)
    {
        $this->authorize('view', $region);
        // تحميل العلاقات حسب الطلب
        if ($request->boolean('with_branch')) {
            $region->load('branch');
        }

        return $this->success(
            new RegionResource($region->loadCount('mosques')),
            'بيانات المنطقة'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdateRegionRequest  $request
     * @param  Region  $region
     */
    public function update(UpdateRegionRequest $request, Region $region)
    {
        $region->update($request->validated());

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_branch')) {
            $region->load('branch');
        }

        return $this->success(
            new RegionResource($region),
            'تم تحديث بيانات المنطقة بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  Region  $region
     */
    public function destroy(Region $region)
    {
        $this->authorize('delete', $region);
        // تحقق من وجود مساجد تابعة قبل الحذف
        if ($region->mosques()->exists()) {
            return $this->error(
                'لا يمكن حذف المنطقة لأنها تحتوي على مساجد تابعة',
                400
            );
        }

        $region->delete();

        return $this->success(
            null,
            'تم حذف المنطقة بنجاح'
        );
    }
}
