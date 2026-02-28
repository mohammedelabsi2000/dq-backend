<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCenterRequest;
use App\Http\Requests\UpdateCenterRequest;
use App\Http\Resources\CenterResource;
use App\Http\Traits\ApiResponser;
use App\Models\Center;
use Illuminate\Http\Request;

class CenterController extends Controller
{
    use ApiResponser; // استخدم الـ Trait

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Center::query();

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


        if ($request->boolean('with_mosque')) {
            $query->with('mosque');
        }

        $perPage = $request->integer('per_page', 15);
        $centers = $query->latest()->paginate($perPage);

        // return $this->success(
        //     [
        //         'items' => CenterResource::collection($centers),
        //         'pagination' => $this->paginate($centers),
        //     ],
        //     'قائمة المراكز'
        // );

        return $this->apiResponse([
            'total' => $total,
            'skip' => $q['skip'],
            'limit' => $q['limit'],
            'data' => CenterResource::collection($centers),
        ], 'success', 200);

        // return response()->json(
        //     $query->latest()->paginate(15),
        //     200
        // );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreCenterRequest $request)
    {
        $center = Center::create($request->validated());

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_mosque')) {
            $center->load('mosque');
        }

        // ✅ استخدام success مع البيانات والرسالة وكود 201
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
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Center $center)
    {
        if ($request->boolean('with_mosque')) {
            $center->load('mosque');
        }

        // ✅ استخدام success
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
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateCenterRequest $request, Center $center)
    {
        $center->update($request->validated());
        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_mosque')) {
            $center->load('mosque');
        }

        // ✅ استخدام success
        return $this->success(
            new CenterResource($center),
            'تم تحديث بيانات المركز بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Center $center)
    {
        // تحقق من وجود حلقات تابعة قبل الحذف
        if ($center->halaqat()->exists()) {
            // ✅ استخدام error
            return $this->error(
                'لا يمكن حذف المركز لأنه يحتوي على حلقات تابعة',
                400
            );
        }

        $center->delete();

        // ✅ استخدام success مع null
        return $this->success(
            null,
            'تم حذف المركز بنجاح'
        );
    }
}
