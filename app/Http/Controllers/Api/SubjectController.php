<?php

namespace App\Http\Controllers;

use App\Http\Requests\SubjectRequest;
use App\Http\Resources\SubjectResource;
use App\Models\Subject;
use App\Models\Track;
use Illuminate\Http\JsonResponse;

class SubjectController extends Controller
{
    public function index(Track $track): JsonResponse
    {
        $subjects = $track->subjects()->latest()->paginate(15);

        return response()->json(SubjectResource::collection($subjects)->response()->getData(true));
    }

    public function store(SubjectRequest $request): JsonResponse
    {
        $subject = Subject::create($request->validated());

        return response()->json(new SubjectResource($subject->load('track')), 201);
    }

    public function show(Subject $subject): JsonResponse
    {
        $subject->load('track');

        return response()->json(new SubjectResource($subject));
    }

    public function update(SubjectRequest $request, Subject $subject): JsonResponse
    {
        $subject->update($request->validated());

        return response()->json(new SubjectResource($subject));
    }

    public function destroy(Subject $subject): JsonResponse
    {
        $subject->delete();

        return response()->json(['message' => 'تم حذف المساق بنجاح']);
    }
}
