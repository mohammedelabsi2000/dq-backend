<?php

namespace App\Http\Controllers\Api;

use App\Enums\HalaqaReferenceType;
use App\Filters\HalaqaFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Halaqa\HalaqaRequest;
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

        $filteredQuery = (new HalaqaFilter($query, $request))->apply();

        // Filter by specific center
        if ($request->filled('center_id')) {
            $query->whereHasMorph(
                'reference',
                [HalaqaReferenceType::Center->value],
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
                $q->whereHasMorph('reference', [HalaqaReferenceType::Region->value], function ($regionQuery) use ($regionId) {
                    $regionQuery->where('id', $regionId);
                })
                    // Halaqas under centers in this region
                    ->orWhereHasMorph('reference', [HalaqaReferenceType::Center->value], function ($centerQuery) use ($regionId) {
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

        if ($request->boolean('with_students')) {
            $filteredQuery->with('students');
        }

        $query = $q['query'];
        $total = $q['count'];
        $halaqas = $query->with(['reference', 'type', 'students', 'supervisors.user'])->get();

        return $this->successWithPagination(
            HalaqaResource::collection($halaqas),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param HalaqaRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(HalaqaRequest $request)
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
     * @param  Request  $request
     * @param  Halaqa  $halaqa
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
     * Update the specified resource in storage.
     *
     * @param HalaqaRequest $request
     * @param Halaqa $halaqa
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(HalaqaRequest $request, Halaqa $halaqa)
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
     * @param  Halaqa  $halaqa
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Halaqa $halaqa)
    {
        $this->authorize('delete', $halaqa);
        if ($halaqa->students()->exists()) {
            return $this->error(
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
