<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Http\Requests\UpdateBranchRequest;
use App\Http\Resources\BranchResource;
use App\Http\Traits\ApiResponser;
use App\Models\Branch;
use Illuminate\Http\Request;

class BranchController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $query = Branch::query();

        // بحث في الاسم
        if ($request->filled('search')) {
            $query->where('name', 'LIKE', '%' . $request->search . '%');
        }

        // فلترة حسب الحد الأقصى
        if ($request->filled('max_replacement_limit')) {
            $query->where('max_replacement_limit', '<=', $request->integer('max_replacement_limit'));
        }

        // فلترة حسب الحد الأدنى
        if ($request->filled('min_replacement_limit')) {
            $query->where('min_replacement_limit', '>=', $request->integer('min_replacement_limit'));
        }

        // تحميل العلاقات
        if ($request->boolean('with_regions')) {
            $query->with('regions');
        }

        // إضافة عدد المناطق
        if ($request->boolean('with_regions_count')) {
            $query->withCount('regions');
        }

        $perPage = $request->integer('per_page', 15);
        $branches = $query->latest()->paginate($perPage);

        return $this->success(
            [
                'items' => BranchResource::collection($branches),
                'pagination' => $this->paginate($branches),
            ],
            'قائمة الفروع'
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

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_regions')) {
            $branch->load('regions');
        }

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
        // تحميل العلاقات حسب الطلب
        if ($request->boolean('with_regions')) {
            $branch->load('regions');
        }

        if ($request->boolean('with_regions_count')) {
            $branch->loadCount('regions');
        }

        return $this->success(
            new BranchResource($branch),
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
        $branch->update($request->validated());

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_regions')) {
            $branch->load('regions');
        }

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
