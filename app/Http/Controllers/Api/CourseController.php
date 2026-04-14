<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CourseResource;
use App\Models\Course;
use Illuminate\Http\Request;

class CourseController extends Controller
{

    /**
     * Display a listing of courses.
     */
    public function index(Request $request)
    {
        $perPage = $request->integer('per_page', 15);

        $courses = Course::with('track')->latest()->paginate($perPage);

        return $this->success(
            CourseResource::collection($courses),
            'تم جلب الدورات بنجاح',
            200
        );
    }

    /**
     * Store a newly created course.
     */
    public function store(Request $request)
    {
        $request->validate([
            'track_id' => 'required|exists:tracks,id',
            'name' => 'required',
            'book_name' => 'nullable',
            'hours' => 'required|integer',
            'max_score' => 'required|integer'
        ]);

        $course = Course::create($request->all());

        return $this->success(
            new CourseResource($course),
            'تم إنشاء الدورة',
            201
        );
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course)
    {
        $course->load('track');

        return $this->success(
            new CourseResource($course),
            'تم جلب الدورة',
            200
        );
    }

    /**
     * Update the specified course.
     */
    public function update(Request $request, Course $course)
    {
        $request->validate([
            'track_id' => 'required|exists:tracks,id',
            'name' => 'required',
            'hours' => 'required|integer',
            'max_score' => 'required|integer'
        ]);

        $course->update($request->all());

        return $this->success(
            new CourseResource($course->fresh()),
            'تم التعديل',
            200
        );
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Course $course)
    {
        $course->delete();

        return $this->success(
            null,
            'تم الحذف',
            200
        );
    }
}
