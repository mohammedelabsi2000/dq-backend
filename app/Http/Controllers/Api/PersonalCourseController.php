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
        $data = PersonalCourse::with(['person'])->get();

        return $this->apiResponse(
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

        return response()->json([
            'message' => 'Personal course created successfully',
            'data' => $course->load(['person']),
        ], 201);
    }

    public function show(PersonalCourse $personalCourse)
    {
        $personalCourse = $personalCourse->load(['person']);

        return $this->apiResponse(
            new PersonalCourseResource($personalCourse),
            'success',
            200
        );
    }

    public function update(UpdatePersonalCourseRequest $request, PersonalCourse $personalCourse)
    {
        $personalCourse->update($request->validated());

        return response()->json([
            'message' => 'Personal course updated successfully',
            'data' => $personalCourse->load(['person']),
        ]);
    }

    public function destroy(PersonalCourse $personalCourse)
    {
        $personalCourse->delete();

        return response()->json([
            'message' => 'Personal course deleted successfully',
        ], 204);
    }
}
