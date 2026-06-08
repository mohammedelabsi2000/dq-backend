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
        $data = $request->validated();
        // Encode numeric arrays as JSON numbers without quotes, so they are stored as JSON arrays in the database
        $data['juzs'] = json_encode(
            array_map('intval', $data['juzs'] ?? [])
        );
        $data['surahs'] = json_encode(
            array_map('intval', $data['surahs'] ?? [])
        );
        $data['verses'] = json_encode(
            array_map('intval', $data['verses'] ?? [])
        );
        $data['pages'] = json_encode(
            array_map('intval', $data['pages'] ?? [])
        );

        $subject = Subject::create($data);

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

        $data['juzs'] = json_encode(
            array_map('intval', $data['juzs'] ?? [])
        );
        $data['surahs'] = json_encode(
            array_map('intval', $data['surahs'] ?? [])
        );
        $data['verses'] = json_encode(
            array_map('intval', $data['verses'] ?? [])
        );
        $data['pages'] = json_encode(
            array_map('intval', $data['pages'] ?? [])
        );

        $subject->update($data);

        return $this->success(new SubjectResource($subject), 'تم تحديث المساق بنجاح', 200);
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $subject->delete();

        return $this->success(null, 'تم حذف المساق بنجاح', 200);
    }
}
