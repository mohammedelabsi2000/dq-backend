<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonalCourse\StorePersonalCourseRequest;
use App\Http\Requests\PersonalCourse\UpdatePersonalCourseRequest;
use App\Http\Resources\PersonalCourseResource;
use App\Http\Traits\ApiResponser;
use App\Models\PersonalCourse;

class PersonalCourseController extends Controller
{
    use ApiResponser;
    public function index()
    {
        $personalCourse = PersonalCourse::with(['person', 'type'])->get();
        return $this->apiResponse(
            PersonalCourseResource::collection($personalCourse),
            'success',
            200
        );
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
        $personalCourse = $personalCourse->load(['person', 'type']);
        
        return $this->apiResponse(
            new PersonalCourseResource($personalCourse),
            'success',
            200
        );
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