<?php

namespace App\Http\Controllers;

use App\Http\Requests\LevelTrackCourseRequest;
use App\Http\Resources\LevelTrackCourseResource;
use App\Models\LevelTrack;
use App\Models\LevelTrackCourse;
use Illuminate\Http\JsonResponse;

class LevelTrackCourseController extends Controller
{
    public function index(LevelTrack $levelTrack): JsonResponse
    {
        $courses = $levelTrack->levelTrackCourses()->with('course')->get();

        return response()->json(LevelTrackCourseResource::collection($courses));
    }

    public function store(LevelTrackCourseRequest $request): JsonResponse
    {
        $levelTrackCourse = LevelTrackCourse::create($request->validated());

        return response()->json(
            new LevelTrackCourseResource($levelTrackCourse->load('course', 'levelTrack')),
            201
        );
    }

    public function show(LevelTrackCourse $levelTrackCourse): JsonResponse
    {
        $levelTrackCourse->load('course', 'levelTrack.level', 'levelTrack.track');

        return response()->json(new LevelTrackCourseResource($levelTrackCourse));
    }

    public function update(LevelTrackCourseRequest $request, LevelTrackCourse $levelTrackCourse): JsonResponse
    {
        $levelTrackCourse->update($request->validated());

        return response()->json(new LevelTrackCourseResource($levelTrackCourse));
    }

    public function destroy(LevelTrackCourse $levelTrackCourse): JsonResponse
    {
        $levelTrackCourse->delete();

        return response()->json(['message' => 'تم حذف المساق من المستوى بنجاح']);
    }
}
