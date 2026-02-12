<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\AcademicQualification\StoreAcademicQualificationRequest;
use App\Http\Requests\AcademicQualification\UpdateAcademicQualificationRequest;
use App\Models\AcademicQualification;

class AcademicQualificationController extends Controller
{
    
    public function index()
    {
        $data = AcademicQualification::with([
            'academicDegree',
            'major',
            'person',
        ])->paginate();

        return response()->json($data);
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
        return response()->json(
            $academicQualification->load([
                'academicDegree',
                'major',
                'person',
            ])
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
