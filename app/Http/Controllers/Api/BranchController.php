<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Branch\StoreBranchRequest;
use App\Http\Requests\Branch\UpdateBranchRequest;
use App\Http\Resources\BranchResource;
use App\Http\Traits\ApiResponser;
use App\Models\Branch;
use Illuminate\Http\Request;
use App\Traits\QueryFilterTrait;
use Illuminate\Support\Facades\Gate;

class BranchController extends Controller
{
    use ApiResponser, QueryFilterTrait;

    public function index(Request $request)
    {
        $this->authorize('viewAny', Branch::class);

        // $query = Branch::query();
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

        // $perPage  = $request->integer('per_page', 15);
        // $branches = $query->withCount('regions')->latest()->paginate($perPage);

        // $branches = $query->get();

        return $this->apiResponse([
            'total' => $total,
            'skip'  => $q['skip'],
            'limit' => $q['limit'],
            'data'  => BranchResource::collection($branches),
        ], 'success', 200);
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
     * @param  int  $id
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
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(UpdateBranchRequest $request, Branch $branch)
    {
        // $this->authorize('update', $branch);

        $branch->update($request->validated());

        return $this->success(
            new BranchResource($branch),
            'تم تحديث بيانات الفرع بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy(Branch $branch)
    {
        $this->authorize('delete', $branch);
        // تحقق من وجود مناطق تابعة قبل الحذف
        if ($branch->regions()->exists()) {
            return $this->errorMessage(
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
