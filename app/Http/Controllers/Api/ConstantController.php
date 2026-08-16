<?php

namespace App\Http\Controllers\Api;

use App\Filters\ConstantFilter;
use App\Http\Controllers\Controller;
use App\Http\Requests\Constant\StoreConstantRequest;
use App\Http\Requests\Constant\UpdateConstantRequest;
use App\Http\Resources\ConstantResource;
use Illuminate\Http\Request;
use App\Models\Constant;
use App\Models\ConstantType;
use Illuminate\Database\Eloquent\ModelNotFoundException;


class ConstantController extends Controller
{

    /**
     * Summary of index
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', Constant::class);
        $query = Constant::query();

        $query = (new ConstantFilter($query, $request))->apply();

        // لإرجاع قائمة بالثوابت من نوع مخصص
        if ($request->filled('with_type_name')) {
            /* try {
                $constType = ConstantType::where(
                    'name',
                    'like',
                    request()->get('with_type_name')
                )->firstOrFail();
            } catch (ModelNotFoundException $th) {
                return $this->error('نوع الثوابت هذا غير موجود في النظام');
            }
            $query->where('constant_type_id', '=', intval($constType['id'])); */

            $query->whereIn('constant_type_id', function ($subQuery) use ($request) {
                $subQuery->select('id')
                    ->from('constant_types')
                    ->whereIn('name', explode(',', $request->get('with_type_name')));
            });

            

            $q = $this->applyFilters($query, [
                'searchColumns' => ['name'],
                'orderColumn' => 'created_at',
                'limit' => '*',
            ]);

            $query = $q['query'];
            $total = $q['count'];

            $constants = $query->leftJoin('constant_types', 'constants.constant_type_id', '=', 'constant_types.id')
            ->get(['constants.id', 'constants.name', 'constants.const_key', 'constant_types.name as type_name'])
            ->groupBy('type_name');
            return $this->successWithPagination(
                $constants,
                ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
                'success',
                200
            );
        }

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        // لإرجاع جميع ثوابت النظام
        $constants = $query->with(['constantType', 'parent'])->get();

        return $this->successWithPagination(
            ConstantResource::collection($constants),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    /**
     * Summary of show
     * @param Constant $constant
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Constant $constant)
    {
        $this->authorize('view', $constant);
        $constant = $constant->load([
            'parent',
            'constantType'
        ]);

        return $this->success(
            new ConstantResource($constant),
            'success',
            200
        );
    }

    /**
     * Summary of store
     * @param StoreConstantRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreConstantRequest $request)
    {
        $constant = Constant::create($request->validated());
        return $this->success(
            new ConstantResource($constant->load([
                'parent',
                'constantType'
            ])),
            'تم إنشاء الثابت بنجاح',
            201
        );
    }

    /**
     * Summary of update
     * @param UpdateConstantRequest $request
     * @param Constant $constant
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateConstantRequest $request, Constant $constant)
    {
        $constant->update($request->validated());
        $constant = $constant->load([
            'parent',
            'constantType'
        ]);
        return $this->success(
            new ConstantResource($constant),
            'تم تحديث بيانات الثابت بنجاح'
        );
    }

    /**
     * Summary of destroy
     * @param Constant $constant
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Constant $constant)
    {
        $this->authorize('delete', $constant);
        if ($constant->isUsed()) {
            return $this->error(
                'لا يمكن حذف هذا الثابت لأنه مستخدم في سجلات أخرى.',
                400
            );
        }

        $constant->delete();

        return $this->success(
            null,
            'تم حذف الثابت بنجاح'
        );
    }
}
