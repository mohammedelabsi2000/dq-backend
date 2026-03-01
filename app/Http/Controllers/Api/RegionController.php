<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRegionRequest;
use App\Http\Requests\UpdateRegionRequest;
use App\Http\Resources\RegionResource;
use App\Http\Traits\ApiResponser;
use App\Models\Region;
use App\Traits\QueryFilterTrait;
use Illuminate\Http\Request;

class RegionController extends Controller
{
    // use ApiResponser, QueryFilterTrait;

    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $query = Region::query();

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

        // $regions = $query->with(['branch'])->get();
        $perPage = $request->integer('per_page', 15);
        $regions = $query->withCount('mosques')->latest()->paginate($perPage);

        return $this->apiResponse([
            'total' => $total,
            'skip' => $q['skip'],
            'limit' => $q['limit'],
            'data' => RegionResource::collection($regions),
        ], 'success', 200);
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
     * @param  int  $id
     */
    public function show(Request $request, Region $region)
    {
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
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
     * @param  int  $id
     */
    public function destroy(Region $region)
    {
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
