<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreHalaqaRequest;
use App\Http\Requests\UpdateHalaqaRequest;
use App\Http\Resources\HalaqaResource;
use App\Http\Traits\ApiResponser;
use App\Models\Halaqa;
use Illuminate\Http\Request;

class HalaqaController extends Controller
{
    use ApiResponser;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Halaqa::query();

        if ($request->filled('center_id')) {
            $query->where('center_id', $request->integer('center_id'));
        }

        if ($request->filled('constant_id')) {
            $query->where('constant_id', $request->integer('constant_id'));
        }

        if ($request->boolean('with_center')) {
            $query->with('center');
        }

        if ($request->boolean('with_constant')) {
            $query->with('constant');
        }

        $perPage = $request->integer('per_page', 15);
        $halaqas = $query->latest()->paginate($perPage);

        return $this->success(
            [
                'items' => HalaqaResource::collection($halaqas),
                'pagination' => $this->paginate($halaqas),
            ],
            'قائمة الحلقات'
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreHalaqaRequest $request)
    {
        $halaqa = Halaqa::create($request->validated());

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_center')) {
            $halaqa->load('center');
        }

        if ($request->boolean('with_constant')) {
            $halaqa->load('constant');
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
     * @return \Illuminate\Http\Response
     */
    public function show(Request $request, Halaqa $halaqa)
    {
        if ($request->boolean('with_center')) {
            $halaqa->load('center');
        }

        if ($request->boolean('with_constant')) {
            $halaqa->load('constant');
        }


        return $this->success(
            new HalaqaResource($halaqa),
            'بيانات الحلقة'
        );
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateHalaqaRequest $request, Halaqa $halaqa)
    {
        $halaqa->update($request->validated());

        // تحميل العلاقات إذا طلب
        if ($request->boolean('with_center')) {
            $halaqa->load('center');
        }

        if ($request->boolean('with_constant')) {
            $halaqa->load('constant');
        }

        return $this->success(
            new HalaqaResource($halaqa),
            'تم تحديث بيانات الحلقة بنجاح'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Halaqa $halaqa)
    {
        $halaqa->delete();

        return $this->success(
            null,
            'تم حذف الحلقة بنجاح'
        );
    }
}
