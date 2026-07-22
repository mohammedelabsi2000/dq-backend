<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Certificate\CertificateRequest;
use App\Http\Resources\Certificate\CertificateResource;
use App\Models\Certificate;
use App\Models\Image;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;

class CertificateController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Certificate::class);
        $query = Certificate::query();

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
            CertificateResource::collection($data),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    public function store(CertificateRequest $request)
    {
        $certificate = Certificate::create($request->validated());

        if ($request->hasFile('certificate_file')) {
            // delegate file processing to the helper
            $this->storeCertificate($certificate, $request->file('certificate_file'));
        }

        return $this->success(
            new CertificateResource($certificate->load(['academicDegree', 'major', 'person', 'images'])),
            'تم إضافة الشهادة بنجاح',
            201
        );
    }

    public function show(Certificate $certificate)
    {
        $this->authorize('view', $certificate);
        $certificate = $certificate->load([
            'academicDegree',
            'major',
            'person',
            'images',

        ]);

        return $this->success(
            new CertificateResource($certificate),
            'success',
            200
        );
    }

    public function update(CertificateRequest $request, Certificate $certificate)
    {

        $certificate->update($request->validated());

        if ($request->hasFile('certificate_file')) {
            $this->storeCertificate($certificate, $request->file('certificate_file'));
        }

        return $this->success(
            new CertificateResource($certificate->load(['academicDegree', 'major', 'person', 'images'])),
            'تم تحديث بيانات الشهادة',
            200
        );
    }

    public function destroy(Certificate $certificate)
    {
        $this->authorize('delete', $certificate);
        $certificate->delete();

        return $this->success(
            null,
            'تم حذف الشهادة',
            202
        );
    }

    protected function storeCertificate(Certificate $certificate, UploadedFile $file)
    {
        $path = $file->store('uploads/certificates', 'public');

        return Image::updateOrCreate(
            [
                'imageable_id' => $certificate->id,
                'imageable_type' => Certificate::class,
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

    public function getPersonCertificates($person_type, $person_id)
    {
        $this->authorize('viewAny', Certificate::class);
        $data = Certificate::with([
            'academicDegree',
            'major',
            'images'
        ])->where('person_type', $person_type)
            ->where('person_id', $person_id)
            ->get();

        return $this->success(
            CertificateResource::collection($data),
            'success',
            200
        );
    }
}
