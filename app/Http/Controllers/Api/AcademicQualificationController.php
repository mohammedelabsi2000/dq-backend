<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Http\Requests\AcademicQualification\StoreAcademicQualificationRequest;
use App\Http\Requests\AcademicQualification\UpdateAcademicQualificationRequest;
use App\Http\Resources\AcademicQualificationResource;
use App\Models\AcademicQualification;
use App\Models\Image;

class AcademicQualificationController extends Controller
{

    public function getPersonQualifications($person_type, $person_id)
    {
        $data = AcademicQualification::with([
            'academicDegree',
            'major',
            'person',
            'images'
        ])->where('person_type', $person_type)
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
        $query = AcademicQualification::query();

        $q = $this->applyFilters($query, [
            'searchColumns' => [''],
            'orderColumn' => 'created_at',
            'orderBy' => 'desc'
        ]);

        $query = $q['query'];
        $total = $q['count'];
        // $total = $query->count();
        $data = $query->with([
            'academicDegree',
            'major',
            'person',
            'images',
        ])->get();

        return $this->apiResponse([
            'total' => $total,
            'skip' => $q['skip'],
            'limit' => $q['limit'],
            'data' => AcademicQualificationResource::collection($data),
        ], 'success', 200);
    }

    public function store(StoreAcademicQualificationRequest $request)
    {

        $qualification = AcademicQualification::create($request->validated());

        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $path = $file->store('uploads/certificates', 'public');

            $image = Image::create([
                'imageable_id' => $qualification->id,
                'imageable_type' => AcademicQualification::class,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'disk' => 'public',
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                // 'image_type' => $validated['image_type'] ?? null,
            ]);
        }

        return $this->success(
            new AcademicQualificationResource($qualification->load(['academicDegree', 'major', 'person', 'images'])),
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

        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $path = $file->store('uploads/certificates', 'public');


            $image = Image::updateOrCreate([
                'imageable_id' => $academicQualification->id,
                'imageable_type' => AcademicQualification::class
            ], [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'disk' => 'public',
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
                // 'image_type' => $validated['image_type'] ?? null,
            ]);
        }


        return $this->success(
            new AcademicQualificationResource($academicQualification->load(['academicDegree', 'major', 'person', 'images'])),
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
