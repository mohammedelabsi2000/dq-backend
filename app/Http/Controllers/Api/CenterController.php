<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Center\StoreCenterRequest;
use App\Http\Requests\Center\UpdateCenterRequest;
use App\Http\Resources\CenterResource;
use App\Http\Traits\ApiResponser;
use App\Models\Center;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    // use ApiResponser; // استخدم الـ Trait

    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Center::class);
        $query = Center::query()->visibleTo(auth()->user());

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];


        if ($request->filled('mosque_id')) {
            $query->where('mosque_id', $request->integer('mosque_id'));
        }

        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
        }

        // if ($request->boolean('with_mosque')) {
        //     $query->with('mosque.region.branch');
        // }
        if ($request->boolean('with_relations')) {
            $query->with(['region.branch', 'mosque']);
        }

        // if ($request->boolean('with_mosque')) {
        //     $query->with('mosque');
        // }
        $centers = $query->get();
        // dd($centers);
        // $centers = $query->withCount('halaqat')->latest()->paginate($perPage);

        return $this->apiResponse([
            'total' => $total,
            'skip' => $q['skip'],
            'limit' => $q['limit'],
            'data' => CenterResource::collection($centers),
        ], 'success', 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCenterRequest $request)
    {
        // $this->authorize('create', Center::class);
        $center = Center::create($request->validated());

        if ($request->boolean('with_mosque')) {
            $center->load('mosque.region.branch');
        }

        if ($request->boolean('with_region')) {
            $center->load('region');
        }
        // // تحميل العلاقات إذا طلب
        // if ($request->boolean('with_mosque')) {
        //     $center->load('mosque');
        // }

        return $this->success(
            new CenterResource($center),
            'تم إنشاء المركز بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Center $center)
    {
        $this->authorize('view', $center);
        // if ($request->boolean('with_mosque')) {
        //     $center->load('mosque');
        // }

        if ($request->boolean('with_mosque')) {
            $center->load('mosque.region.branch');
        }

        if ($request->boolean('with_region')) {
            $center->load('region.branch');
        }

        return $this->success(
            new CenterResource($center),
            'بيانات المركز'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCenterRequest $request, Center $center)
    {
        // $this->authorize('update', $center);
        $center->update($request->validated());
        // // تحميل العلاقات إذا طلب
        // if ($request->boolean('with_mosque')) {
        //     $center->load('mosque');
        // }

        if ($request->boolean('with_mosque')) {
            $center->load('mosque.region.branch');
        }

        if ($request->boolean('with_region')) {
            $center->load('region.branch');
        }

        return $this->success(
            new CenterResource($center),
            'تم تحديث بيانات المركز بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Center $center)
    {
        // if ($center->halaqat()->exists()) {
        //     return $this->errorMessage(
        //         'لا يمكن حذف المركز لأنه يحتوي على حلقات تابعة',
        //         400
        //     );
        // }
        $this->authorize('delete', $center);

        $center->delete();

        return $this->success(null, 'تم حذف المركز بنجاح');
    }
}
