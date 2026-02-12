<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonalCourse\StorePersonalCourseRequest;
use App\Http\Requests\PersonalCourse\UpdatePersonalCourseRequest;
use App\Models\PersonalCourse;

class PersonalCourseController extends Controller
{
    public function index()
    {
        return PersonalCourse::with(['person', 'type'])->get();
    }

    public function store(StorePersonalCourseRequest $request)
    {
        //$course = PersonalCourse::create($request->validated());

        // attach polymorphic person
        /*$course->person()->associate([
            'id' => $request->person_id,
            'type' => $request->person_type,
        ]);
        $course->save();

        return response()->json($course, 201);*/

        $course = new PersonalCourse($request->validated());

        // الحصول على الـ person instance polymorphic
        $personType = $request->person_type; // مثال: App\Models\User
        $personId = $request->person_id;

        $person = $personType::findOrFail($personId);

        // ربط polymorphic relation
        $course->person()->associate($person);

        $course->save();

        return response()->json($course->load(['person', 'type']), 201);
    }

    public function show(PersonalCourse $personalCourse)
    {
        return $personalCourse->load(['person', 'type']);
    }

    public function update(UpdatePersonalCourseRequest $request, PersonalCourse $personalCourse)
    {
        $personalCourse->update($request->validated());
        return response()->json($personalCourse);
    }

    public function destroy(PersonalCourse $personalCourse)
    {
        $personalCourse->delete();
        return response()->noContent();
    }
}