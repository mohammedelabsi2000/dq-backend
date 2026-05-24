<?php

namespace App\Http\Controllers;

use App\Http\Requests\CourseRequest;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use App\Models\Track;
use Illuminate\Http\JsonResponse;

class CourseController extends Controller
{
    public function index(Track $track): JsonResponse
    {
        $courses = $track->courses()->latest()->paginate(15);

        return response()->json(CourseResource::collection($courses)->response()->getData(true));
    }

    public function store(CourseRequest $request): JsonResponse
    {
        $course = Course::create($request->validated());

        return response()->json(new CourseResource($course->load('track')), 201);
    }

    public function show(Course $course): JsonResponse
    {
        $course->load('track');

        return response()->json(new CourseResource($course));
    }

    public function update(CourseRequest $request, Course $course): JsonResponse
    {
        $course->update($request->validated());

        return response()->json(new CourseResource($course));
    }

    public function destroy(Course $course): JsonResponse
    {
        $course->delete();

        return response()->json(['message' => 'تم حذف المساق بنجاح']);
    }
}
