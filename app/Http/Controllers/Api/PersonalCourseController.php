<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\PersonalCourse\StorePersonalCourseRequest;
use App\Http\Requests\PersonalCourse\UpdatePersonalCourseRequest;
use App\Http\Resources\PersonalCourseResource;
use App\Models\Image;
use App\Models\PersonalCourse;
use App\Models\Student;
use App\Models\User;

class PersonalCourseController extends Controller
{

    public function getPersonCourses($person_type, $person_id)
    {
        $this->authorize('viewForPerson', [PersonalCourse::class, $person_type, (int) $person_id]);

        $data = PersonalCourse::with(['person', 'type', 'images'])
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
        $this->authorize('viewAny', PersonalCourse::class);
        $query = PersonalCourse::query();

        if (!auth()->user()->isGlobalAdmin()) {
            $query->where(function ($q) {
                $q->where(function ($q) {
                    $q->where('person_type', 'student')
                        ->whereIn('person_id', Student::visibleTo(auth()->user())->select('id'));
                })->orWhere(function ($q) {
                    $q->where('person_type', 'user')
                        ->whereIn('person_id', User::visibleTo(auth()->user())->select('id'));
                });
            });
        }

        $q = $this->applyFilters($query, [
            'searchColumns' => [],
            'orderColumn' => 'created_at',
            'orderBy' => 'desc'
        ]);

        $query = $q['query'];
        $total = $q['count'];
        // $total = $query->count();
        $users = $query->with(['person', 'type', 'images'])->get();

        return $this->successWithPagination(
            PersonalCourseResource::collection($users),
            [
                'total' => $total,
                'skip' => $q['skip'],
                'limit' => $q['limit'],
            ],
            'success',
            200
        );
    }

    public function store(StorePersonalCourseRequest $request)
    {
        $course = PersonalCourse::create($request->validated());

        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $path = $file->store('uploads/certificates', 'public');

            $image = Image::create([
                'imageable_id' => $course->id,
                'imageable_type' => PersonalCourse::class,
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'disk' => 'public',
                'mime_type' => $file->getMimeType(),
                'file_size' => $file->getSize(),
            ]);
        }
        return $this->success(
            new PersonalCourseResource($course->load(['person', 'images', 'type'])),
            'تم إضافة الدورات الشخصية بنجاح',
            201
        );
    }

    public function show(PersonalCourse $personalCourse)
    {
        $this->authorize('view', $personalCourse);
        $personalCourse = $personalCourse->load(['person', 'type']);

        return $this->success(
            new PersonalCourseResource($personalCourse),
            'success',
            200
        );
    }

    public function update(UpdatePersonalCourseRequest $request, PersonalCourse $personalCourse)
    {
        $personalCourse->update($request->validated());

        if ($request->hasFile('certificate_file')) {
            $file = $request->file('certificate_file');
            $path = $file->store('uploads/certificates', 'public');

            $image = Image::updateOrCreate([
                'imageable_id' => $personalCourse->id,
                'imageable_type' => PersonalCourse::class
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
            new PersonalCourseResource($personalCourse->load(['person', 'images', 'type'])),
            'تم تحديث بيانات الدورة بنجاح',
            200
        );
    }

    public function destroy(PersonalCourse $personalCourse)
    {
        $this->authorize('delete', $personalCourse);
        $personalCourse->delete();

        return $this->success(
            null,
            'تم حذف الدورة بنجاح',
            202
        );
    }
}
