<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AcademicQualification\StoreAcademicQualificationRequest;
use App\Http\Requests\AcademicQualification\UpdateAcademicQualificationRequest;
use App\Http\Resources\AcademicQualificationResource;
use App\Models\AcademicQualification;
use App\Models\Image;
use Illuminate\Http\UploadedFile;

class AcademicQualificationController extends Controller
{

//test

    public function getPersonQualifications($person_type, $person_id)
    {
        $this->authorize('viewAny', AcademicQualification::class);
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

    /**
     * Summary of index
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        $this->authorize('viewAny', AcademicQualification::class);
        $query = AcademicQualification::query();

        $q = $this->applyFilters($query, [
            'orderColumn' => 'created_at',
            'orderBy' => 'desc'
        ]);

        $query = $q['query'];
        $total = $q['count'];

        $data = $query->with([
            'academicDegree',
            'major',
            'person',
            'images',
        ])->get();

        return $this->successWithPagination(
            AcademicQualificationResource::collection($data),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    /**
     * Store a newly created academic qualification.
     *
     * @param  \App\Http\Requests\AcademicQualification\StoreAcademicQualificationRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(StoreAcademicQualificationRequest $request)
    {
        $qualification = AcademicQualification::create($request->validated());

        if ($request->hasFile('certificate_file')) {
            // delegate file processing to the helper
            $this->storeCertificate($qualification, $request->file('certificate_file'));
        }

        return $this->success(
            new AcademicQualificationResource($qualification->load(['academicDegree', 'major', 'person', 'images'])),
            'تم إضافة المؤهل العلمي بنجاح',
            201
        );
    }

    public function show(AcademicQualification $academicQualification)
    {
        $this->authorize('view', $academicQualification);
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

    /**
     * Update the specified academic qualification.
     *
     * @param  \App\Http\Requests\AcademicQualification\UpdateAcademicQualificationRequest  $request
     * @param  \App\Models\AcademicQualification  $academicQualification
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(
        UpdateAcademicQualificationRequest $request,
        AcademicQualification $academicQualification
    ) {

        $academicQualification->update($request->validated());

        if ($request->hasFile('certificate_file')) {
            $this->storeCertificate($academicQualification, $request->file('certificate_file'));
        }

        return $this->success(
            new AcademicQualificationResource($academicQualification->load(['academicDegree', 'major', 'person', 'images'])),
            'تم تحديث بيانات المؤهل العلمي',
            200
        );
    }

    public function destroy(AcademicQualification $academicQualification)
    {
        $this->authorize('delete', $academicQualification);
        $academicQualification->delete();

        return $this->success(
            null,
            'تم حذف المؤهل العلمي',
            202
        );
    }

    /**
     * Save a certificate file for a qualification.
     *
     * The uploaded file is stored on the public disk and an Image model
     * is created or updated so that the qualification can reference it.
     * This helper hides the storage details and keeps controller actions
     * clean and DRY.
     *
     * @param  AcademicQualification  $qualification  The qualification to attach the image to.
     * @param  UploadedFile          $file           The uploaded certificate file.
     * @return Image|\Illuminate\Database\Eloquent\Model
     */
    protected function storeCertificate(AcademicQualification $qualification, UploadedFile $file)
    {
        $path = $file->store('uploads/certificates', 'public');

        return Image::updateOrCreate(
            [
                'imageable_id' => $qualification->id,
                'imageable_type' => AcademicQualification::class,
            ],
            [
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'disk' => 'public',
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]
        );
    }
}
