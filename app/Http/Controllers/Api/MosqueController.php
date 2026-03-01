<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMosqueRequest;
use App\Http\Requests\UpdateMosqueRequest;
use App\Http\Resources\MosqueResource;
use App\Http\Traits\ApiResponser;
use App\Models\Mosque;
use App\Traits\QueryFilterTrait;
use Illuminate\Http\Request;

class MosqueController extends Controller
{
    // use ApiResponser, QueryFilterTrait;
    /**
     * Display a listing of the resource.
     *
     */
    public function index(Request $request)
    {
        $query = Mosque::query();

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        // فلترة حسب المنطقة
        if ($request->filled('region_id')) {
            $query->where('region_id', $request->integer('region_id'));
        }

        if ($request->boolean('with_region')) {
            $query->with('region.branch');
        }

        // $mosques = $query->with(['region', 'region.branch'])->get();
        $perPage = $request->integer('per_page', 15);
        $mosques = $query->withCount('centers')->latest()->paginate($perPage);

        return $this->apiResponse([
            'total' => $total,
            'skip' => $q['skip'],
            'limit' => $q['limit'],
            'data' => MosqueResource::collection($mosques),
        ], 'success', 200);
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreMosqueRequest $request)
    {
        $mosque = Mosque::create($request->validated());

        if ($request->boolean('with_region')) {
            $mosque->load('region.branch');
        }

        // if ($request->boolean('with_region')) {
        //     $mosque->load('region');
        // }

        // if ($request->boolean('with_region_and_branch')) {
        //     $mosque->load('region.branch');
        // }

        return $this->success(
            new MosqueResource($mosque),
            'تم إنشاء المسجد بنجاح',
            201
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     */
    public function show(Request $request, Mosque $mosque)
    {
        // تحميل العلاقات حسب الطلب
        // if ($request->boolean('with_region')) {
        //     $mosque->load('region');
        // }

        // if ($request->boolean('with_centers')) {
        //     $mosque->load('centers');
        // }

        // if ($request->boolean('with_users')) {
        //     $mosque->load('users');
        // }

        // if ($request->boolean('with_centers_count')) {
        //     $mosque->loadCount('centers');
        // }

        // if ($request->boolean('with_users_count')) {
        //     $mosque->loadCount('users');
        // }
        if ($request->boolean('with_region')) {
            $mosque->load('region.branch');
        }

        return $this->success(
            new MosqueResource($mosque),
            'بيانات المسجد'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(UpdateMosqueRequest $request, Mosque $mosque)
    {
        $mosque->update($request->validated());

        // // تحميل العلاقات إذا طلب
        // if ($request->boolean('with_region')) {
        //     $mosque->load('region');
        // }
        if ($request->boolean('with_region')) {
            $mosque->load('region.branch');
        }

        return $this->success(
            new MosqueResource($mosque),
            'تم تحديث بيانات المسجد بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     */
    public function destroy(Mosque $mosque)
    {
        // تحقق من وجود مراكز تابعة قبل الحذف
        if ($mosque->centers()->exists()) {
            return $this->errorMessage(
                'لا يمكن حذف المسجد لأنه يحتوي على مراكز تابعة',
                400
            );
        }

        // // تحقق من وجود مستخدمين تابعين قبل الحذف
        // if ($mosque->users()->exists()) {
        //     return $this->error(
        //         'لا يمكن حذف المسجد لأنه يحتوي على مستخدمين تابعين',
        //         400
        //     );
        // }

        $mosque->delete();

        return $this->success(
            null,
            'تم حذف المسجد بنجاح'
        );
    }
}
