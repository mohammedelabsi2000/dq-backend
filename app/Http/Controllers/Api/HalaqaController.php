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
        /* Relation::morphMap([
            'Center' => \App\Models\Center::class,
            'Region' => \App\Models\Region::class,
        ]); */

        $query = Halaqa::query()->visibleTo(auth()->user());

        if ($request->filled('center_id')) {
            // If center_id is provided, filter halaqas based on the center
            $query->whereHasMorph(
                'reference',
                ['center'],
                function ($query) {
                    $query->where('id', request()->integer('center_id'));
                }
            );
        } elseif ($request->filled('region_id')) {
            // If region_id is provided (and center_id is not), filter halaqas based on the region
            $query->whereHasMorph(
                'reference',
                ['region'],
                function ($query) {
                    $query->where('id', request()->integer('region_id'));
                }
            );
        }

        /* if ($request->filled('reference_type') && $request->filled('reference_id')) {

             // 1️⃣ نوع المرجع من request (مثلاً "user" أو "school")
             $typeKey = $request->input('reference_type');

             if (!class_exists($typeKey)) {
                 return $this->validationError([$typeKey . ' مرجع غير صالح']);
             }

             $referenceId = request()->integer('reference_id');

             $query->whereHasMorph(
                 'reference',
                 [$typeKey],
                 function ($query) use ($referenceId) {
                     $query->where('id', $referenceId);
                 }
             );
         }*/

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
        $halaqas = $query->with(['reference', 'type'])->get();

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
        $halaqa->load(['type', 'reference']);

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
