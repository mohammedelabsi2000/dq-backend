<?php

namespace App\Http\Controllers\Api;

use App\Filters\CenterFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Center\StoreCenterRequest;
use App\Http\Requests\Center\UpdateCenterRequest;
use App\Http\Resources\CenterResource;
use App\Models\Center;
use Illuminate\Http\Request;

class CenterController extends Controller
{

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

        $query = (new CenterFilter($query, $request))->apply();

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

        if ($request->filled('branch_id')) {
            $query->whereHas('region', function ($q) use ($request) {
                $q->where('branch_id', $request->integer('branch_id'));
            });
        }

        if ($request->boolean('with_relations')) {
            $query->with(['region.branch', 'mosque']);
        }

        // if ($request->boolean('with_mosque')) {
        //     $query->with('mosque');
        // }
        $centers = $query->get();

        return $this->successWithPagination(
            CenterResource::collection($centers),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreCenterRequest $request)
    {
        $center = Center::create($request->validated());

        if ($request->boolean('with_mosque')) {
            $center->load('mosque.region.branch');
        }

        if ($request->boolean('with_region')) {
            $center->load('region');
        }

        return $this->success(
            new CenterResource($center),
            'تم إنشاء المركز بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param Center $center
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Center $center)
    {
        $this->authorize('view', $center);

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
     * @param UpdateCenterRequest $request
     * @param Center $center
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateCenterRequest $request, Center $center)
    {
        $center->update($request->validated());

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
     * @param Center $center
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Center $center)
    {
        $this->authorize('delete', $center);

        $center->delete();

        return $this->success(null, 'تم حذف المركز بنجاح');
    }
}
