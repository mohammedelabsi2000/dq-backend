<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Track;
use Illuminate\Http\Request;
class CourseController extends Controller
{
    public function index()
    {
        $courses = Course::with('track')->latest()->get();
        return view('courses.index', compact('courses'));
    }

    public function create()
    {
        $tracks = Track::all();
        return view('courses.create', compact('tracks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'track_id' => 'required',
            'name' => 'required',
            'book_name' => 'nullable',
            'hours' => 'required|integer',
            'max_score' => 'required|integer'
        ]);

        Course::create($request->all());

        return redirect()->route('courses.index')
            ->with('success','تم إنشاء الدورة');
    }

    public function show(Course $course)
    {
        return view('courses.show', compact('course'));
    }

    public function edit(Course $course)
    {
        $tracks = Track::all();
        return view('courses.edit', compact('course','tracks'));
    }

    public function update(Request $request, Course $course)
    {
        $request->validate([
            'track_id' => 'required',
            'name' => 'required',
            'hours' => 'required|integer',
        ]);

        $course->update($request->all());

        return redirect()->route('courses.index')
            ->with('success','تم التعديل');
    }

    public function destroy(Course $course)
    {
        $course->delete();
        return back()->with('success','تم الحذف');
    }
}
