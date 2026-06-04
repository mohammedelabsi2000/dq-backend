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
            'searchColumns' => ['title', 'sub_title'],
            'orderColumn' => 'created_at',
            'limit' => '*',
        ]);

        $query = $q['query'];
        $subjects = $query->with('subjectType')->get();

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

        return $this->success(new SubjectResource($subject->load('track')), 'تم إنشاء المساق بنجاح', 201);
    }

    public function show(Subject $subject): JsonResponse
    {
        $subject->load('track');

        return $this->success(new SubjectResource($subject));
    }

    public function update(SubjectRequest $request, Subject $subject): JsonResponse
    {
        $data = $request->validated();

        $subject->update($data);

        return $this->success(new SubjectResource($subject), 'تم تحديث المساق بنجاح', 200);
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $subject->delete();

        return $this->success(null, 'تم حذف المساق بنجاح', 200);
    }
}
