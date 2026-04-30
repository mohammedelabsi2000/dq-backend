<?php

namespace App\Http\Controllers\Api;

use App\Filters\HalaqaStatusFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Halaqa\HalaqaStatusRequest;
use App\Http\Resources\HalaqaStatusResource;
use App\Models\HalaqaStatus;
use Illuminate\Http\Request;

class HalaqaStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $query = HalaqaStatus::query();

        $filteredQuery = (new HalaqaStatusFilter($query, $request))->apply();

        [$query, $skip, $limit, $total] = $this->applyFiltersA($filteredQuery, [
            'searchColumns' => ['name'],
            'orderColumn' => 'sort_order',
        ]);

        $halaqaStatuses = $query->get();

        return $this->successWithPagination(
            HalaqaStatusResource::collection($halaqaStatuses),
            ['total' => $total, 'skip' => $skip, 'limit' => $limit],
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     * 
     * @param HalaqaStatusRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(HalaqaStatusRequest $request)
    {
        $halaqaStatus = HalaqaStatus::create($request->validated());
        return $this->success(
            new HalaqaStatusResource($halaqaStatus),
            'تم إنشاء حالة الحلقة بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     * 
     * @param Request $request
     * @param HalaqaStatus $halaqaStatus
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, HalaqaStatus $halaqaStatus)
    {
        return $this->success(
            new HalaqaStatusResource($halaqaStatus),
            'تم جلب حالة الحلقة بنجاح'
        );
    }

    /**
     * Update the specified resource in storage.
     * 
     * @param HalaqaStatusRequest $request
     * @param HalaqaStatus $halaqaStatus
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(HalaqaStatusRequest $request, HalaqaStatus $halaqaStatus)
    {
        $halaqaStatus->update($request->validated());
        return $this->success(
            new HalaqaStatusResource($halaqaStatus),
            'تم تحديث حالة الحلقة بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     * 
     * @param HalaqaStatus $halaqaStatus
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(HalaqaStatus $halaqaStatus)
    {
        $halaqaStatus->delete();
        return $this->success(
            null,
            'تم حذف حالة الحلقة بنجاح'
        );
    }
}
