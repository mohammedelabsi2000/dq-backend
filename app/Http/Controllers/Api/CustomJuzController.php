<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CustomJuzResource;
use App\Http\Requests\CustomJuz\StoreCustomJuzRequest;
use App\Http\Requests\CustomJuz\UpdateCustomJuzRequest;
use App\Models\Quran\CustomJuz;

class CustomJuzController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', CustomJuz::class);
        $query = CustomJuz::query();
        [$query, $skip, $limit, $total] = $this->applyFiltersA($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'sort_order',
        ]);

        $juz = $query->get();

        return $this->successWithPagination(
            CustomJuzResource::collection($juz),
            ['total' => $total, 'skip' => $skip, 'limit' => $limit],
            'success',
            200
        );
    }

    public function store(StoreCustomJuzRequest $request)
    {
        $juz = CustomJuz::create($request->validated());
        return $this->success($juz, 'تم إنشاء الجزء بنجاح');
    }

    public function show(CustomJuz $juz)
    {
        $this->authorize('view', $juz);
        return $this->success($juz);
    }

    public function update(UpdateCustomJuzRequest $request, CustomJuz $juz)
    {
        $juz->update($request->validated());
        return $this->success($juz, 'تم تحديث بيانات الجزء بنجاح');
    }

    public function destroy(CustomJuz $juz)
    {
        $this->authorize('delete', $juz);
        $juz = $juz->delete();

        return $this->success($juz, 'تم حذف الجزء بنجاح');
    }
}
