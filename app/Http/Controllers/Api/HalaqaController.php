<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Halaqa\StoreHalaqaRequest;
use App\Http\Requests\Halaqa\UpdateHalaqaRequest;
use App\Http\Resources\HalaqaResource;
use App\Models\Halaqa;
use Illuminate\Http\Request;

class HalaqaController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {

        $this->authorize('viewAny', Halaqa::class);

        $query = Halaqa::query()->visibleTo(auth()->user());

        // Filter by branch (through both center and region)
        if ($request->filled('branch_id')) {
            $branchId = $request->integer('branch_id');
            $query->where(function ($q) use ($branchId) {
                // Halaqas under centers in this branch
                $q->whereHasMorph('reference', ['center'], function ($centerQuery) use ($branchId) {
                    $centerQuery->whereHas('region', function ($regionQuery) use ($branchId) {
                        $regionQuery->where('branch_id', $branchId);
                    });
                })
                    // Halaqas directly under regions in this branch
                    ->orWhereHasMorph('reference', ['region'], function ($regionQuery) use ($branchId) {
                        $regionQuery->where('branch_id', $branchId);
                    });
            });
        }

        // Filter by specific center
        if ($request->filled('center_id')) {
            $query->whereHasMorph(
                'reference',
                ['center'],
                function ($query) {
                    $query->where('id', request()->integer('center_id'));
                }
            );
        }

        // Filter by specific region
        if ($request->filled('region_id')) {
            $regionId = $request->integer('region_id');
            $query->where(function ($q) use ($regionId) {
                // Halaqas directly under this region
                $q->whereHasMorph('reference', ['region'], function ($regionQuery) use ($regionId) {
                    $regionQuery->where('id', $regionId);
                })
                    // Halaqas under centers in this region
                    ->orWhereHasMorph('reference', ['center'], function ($centerQuery) use ($regionId) {
                        $centerQuery->where('region_id', $regionId);
                    });
            });
        }
        // Filter by reference type
        if ($request->filled('reference_type')) {
            $query->where('reference_type', $request->input('reference_type'));
        }

        if ($request->filled('type_id')) {
            $query->where('type_id', $request->integer('type_id'));
        }

        if ($request->boolean('with_students')) {
            $query->with('students');
        }

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',
        ]);

        $query = $q['query'];
        $total = $q['count'];
        $halaqas = $query->with(['reference', 'type', 'supervisors.user'])->get();

        return $this->apiResponse([
            'total' => $total,
            'skip' => $q['skip'],
            'limit' => $q['limit'],
            'data' => HalaqaResource::collection($halaqas),
        ], 'success', 200);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreHalaqaRequest $request)
    {
        $halaqa = Halaqa::create($request->validated());

        if ($request->boolean('with_type')) {
            $halaqa->load('type');
        }

        return $this->success(
            new HalaqaResource($halaqa),
            'تم إنشاء الحلقة بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Halaqa $halaqa)
    {
        $this->authorize('view', $halaqa);
        $halaqa->load(['type', 'reference', 'supervisors.user']);

        if ($request->boolean(key: 'with_students')) {
            $halaqa->load('students');
        }

        return $this->success(
            new HalaqaResource($halaqa),
            'بيانات الحلقة'
        );
    }

    /**
     * Summary of update
     * @param UpdateHalaqaRequest $request
     * @param Halaqa $halaqa
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateHalaqaRequest $request, Halaqa $halaqa)
    {
        $halaqa->update($request->validated());

        $halaqa->load(['type', 'reference']);

        return $this->success(
            new HalaqaResource($halaqa),
            'تم تحديث بيانات الحلقة بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Halaqa $halaqa)
    {
        $this->authorize('delete', $halaqa);
        if ($halaqa->students()->exists()) {
            return $this->errorMessage(
                'لا يمكن حذف الحلقة لأنها تحتوي على طلاب',
                400
            );
        }

        $halaqa->delete();

        return $this->success(
            null,
            'تم حذف الحلقة بنجاح'
        );
    }
}
