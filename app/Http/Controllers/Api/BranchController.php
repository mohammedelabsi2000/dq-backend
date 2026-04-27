<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Http\Resources\BranchResource;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{

    public function index(Request $request)
    {
        $this->authorize('viewAny', Branch::class);

        $query = Branch::query()->visibleTo(auth()->user());


        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn'   => 'created_at',
            'limit'         => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];


        // تحميل العلاقات
        if ($request->boolean('with_regions')) {
            $query->with('regions');
        }

        $branches = $query->withCount('regions')->get();

        return $this->successWithPagination(
            BranchResource::collection($branches),
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
    public function store(StoreBranchRequest $request)
    {
        $branch = Branch::create($request->validated());

        return $this->success(
            new BranchResource($branch),
            'تم إنشاء الفرع بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     * 
     * @param Request $request
     * @param Branch $branch
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Branch $branch)
    {
        $this->authorize('view', $branch);
        // تحميل العلاقات حسب الطلب
        if ($request->boolean('with_regions')) {
            $branch->load('regions');
        }

        return $this->success(
            new BranchResource($branch->loadCount('regions')),
            'بيانات الفرع'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateBranchRequest $request
     * @param Branch $branch
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        $branch->update($request->validated());

        return $this->success(
            new BranchResource($branch),
            'تم تحديث بيانات الفرع بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Branch $branch
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Branch $branch)
    {
        $this->authorize('delete', $branch);
        // تحقق من وجود مناطق تابعة قبل الحذف
        if ($branch->regions()->exists()) {
            return $this->error(
                'لا يمكن حذف الفرع لأنه يحتوي على مناطق تابعة',
                400
            );
        }

        $branch->delete();

        return $this->success(
            null,
            'تم حذف الفرع بنجاح'
        );
    }
}
