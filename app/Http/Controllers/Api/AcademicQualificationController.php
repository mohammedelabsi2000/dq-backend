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
    public function index()
    {
        $data = AcademicQualification::with([
            'academicDegree',
            'major',
            'person',
        ])->get();

        return $this->apiResponse(
            AcademicQualificationResource::collection($data),
            'success',
            200
        );
    }

    public function store(StoreAcademicQualificationRequest $request)
    {
        $qualification = AcademicQualification::create($request->validated());

        return response()->json([
            'message' => 'Academic qualification created successfully',
            'data' => $qualification->load(['academicDegree', 'major', 'person']),
        ], 201);
    }

    public function show(AcademicQualification $academicQualification)
    {
        $academicQualification = $academicQualification->load([
            'academicDegree',
            'major',
            'person',
        ]);
        /* return response()->json(
            $academicQualifications
        ); */
        return $this->apiResponse(
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

        return response()->json([
            'message' => 'Academic qualification updated successfully',
            'data' => $academicQualification->load(['academicDegree', 'major', 'person']),
        ]);
    }

    public function destroy(AcademicQualification $academicQualification)
    {
        $academicQualification->delete();

        return response()->json([
            'message' => 'Academic qualification deleted successfully',
        ], 204);
    }
}
