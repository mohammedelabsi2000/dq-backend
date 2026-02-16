<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        $courses = Course::with('track')
            ->latest()
            ->paginate($perPage);

        return response()->json($courses, 200);
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

        return response()->json([
            'message' => 'تم إنشاء الدورة',
            'data' => $course
        ], 201);
    }

    /**
     * Display the specified course.
     */
    public function show(Course $course)
    {
        $course->load('track');

        return response()->json($course, 200);
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

        return response()->json([
            'message' => 'تم التعديل',
            'data' => $course->fresh()
        ], 200);
    }

    /**
     * Remove the specified course.
     */
    public function destroy(Course $course)
    {
        $course->delete();

        return response()->json([
            'message' => 'تم الحذف'
        ], 200);
    }
}
