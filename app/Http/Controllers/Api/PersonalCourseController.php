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

    public function getPersonCourses($person_type, $person_id)
{
    $data = PersonalCourse::with(['person'])
        ->where('person_type', $person_type)
        ->where('person_id', $person_id)
        ->get();

    return $this->success(
        PersonalCourseResource::collection($data),
        'success',
        200
    );
}
    public function index()
    {
        $data = PersonalCourse::with(['person'])->get();

        return $this->success(
            PersonalCourseResource::collection($data),
            'success',
            200
        );
    }

    public function store(StorePersonalCourseRequest $request)
    {
        $course = new PersonalCourse($request->validated());

        $personType = $request->person_type;
        $personId = $request->person_id;

        $person = $personType::findOrFail($personId);

        $course->person()->associate($person);
        $course->save();

        return $this->success(
            new PersonalCourseResource($course->load(['person'])),
            'Personal course created successfully',
            201
        );
    }

    public function show(PersonalCourse $personalCourse)
    {
        $personalCourse = $personalCourse->load(['person']);

        return $this->success(
            new PersonalCourseResource($personalCourse),
            'success',
            200
        );
    }

    public function update(UpdatePersonalCourseRequest $request, PersonalCourse $personalCourse)
    {
        $personalCourse->update($request->validated());

        return $this->success(
            new PersonalCourseResource($personalCourse->load(['person'])),
            'Personal course updated successfully',
            200
        );
    }

    public function destroy(PersonalCourse $personalCourse)
    {
        $personalCourse->delete();

        return $this->success(
            null,
            'Personal course deleted successfully',
            204
        );
    }
}
