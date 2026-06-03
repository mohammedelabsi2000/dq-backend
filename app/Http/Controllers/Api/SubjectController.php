<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Subject\SubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use App\Models\Track;
use Illuminate\Http\JsonResponse;

class SubjectController extends Controller
{
    public function index(): JsonResponse
    {
        $query = Subject::query();

        $q = $this->applyFilters($query, [
            'searchColumns' => ['name'],
            'orderColumn' => 'created_at',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        $subjects = $query->get();
        unset($q['query'], $q['count']); // إزالة الاستعلام والعدد من المصفوفة لتوفير الذاكرة
        return $this->successWithPagination(
            SubjectResource::collection($subjects),
            $q,
            'success',
            200
        );
    }

    public function store(SubjectRequest $request): JsonResponse
    {
        $subject = Subject::create($request->validated());

        return $this->success(new SubjectResource($subject->load('track')), 201);
    }

    public function show(Subject $subject): JsonResponse
    {
        $subject->load('track');

        return $this->success(new SubjectResource($subject));
    }

    public function update(SubjectRequest $request, Subject $subject): JsonResponse
    {
        $subject->update($request->validated());

        return $this->success(new SubjectResource($subject));
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $subject->delete();

        return $this->success(null, 'تم حذف المساق بنجاح', 200);
    }
}
