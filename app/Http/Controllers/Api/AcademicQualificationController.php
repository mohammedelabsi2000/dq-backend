<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Http\Requests\AcademicQualification\StoreAcademicQualificationRequest;
use App\Http\Requests\AcademicQualification\UpdateAcademicQualificationRequest;
use App\Http\Resources\AcademicQualificationResource;
use App\Models\AcademicQualification;

class AcademicQualificationController extends Controller
{
    use ApiResponser;

    public function getPersonQualifications($person_type, $person_id)
    {
        $data = AcademicQualification::with([
    'academicDegree',
    'major',
    'person',
    'images'
])
            ->where('person_type', $person_type)
            ->where('person_id', $person_id)
            ->get();

        return $this->success(
            AcademicQualificationResource::collection($data),
            'success',
            200
        );
    }

    public function index()
    {
        $data = AcademicQualification::with([
            'academicDegree',
            'major',
            'person',
            'images',
        ])->get();

        return $this->success(
            AcademicQualificationResource::collection($data),
            'success',
            200
        );
    }

    public function store(StoreAcademicQualificationRequest $request)
    {
        $qualification = AcademicQualification::create($request->validated());

        return $this->success(
            new AcademicQualificationResource($qualification->load(['academicDegree', 'major', 'person'])),
            'Academic qualification created successfully',
            201
        );
    }

    public function show(AcademicQualification $academicQualification)
    {
        $academicQualification = $academicQualification->load([
            'academicDegree',
            'major',
            'person',
            'images',

        ]);

        return $this->success(
            new AcademicQualificationResource($academicQualification),
            'success',
            200
        );
    }

    public function update(
        UpdateAcademicQualificationRequest $request,
        AcademicQualification $academicQualification
    ) {
        $academicQualification->update($request->validated());

        return $this->success(
            new AcademicQualificationResource($academicQualification->load(['academicDegree', 'major', 'person'])),
            'Academic qualification updated successfully',
            200
        );
    }

    public function destroy(AcademicQualification $academicQualification)
    {
        $academicQualification->delete();

        return $this->success(
            null,
            'Academic qualification deleted successfully',
            204
        );
    }
}
