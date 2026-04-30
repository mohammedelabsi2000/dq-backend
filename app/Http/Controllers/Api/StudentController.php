<?php

namespace App\Http\Controllers\Api;

use App\Enums\HalaqaReferenceType;
use App\Filters\StudentFilter;
use App\Models\Student;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ImportStudentRequest;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Imports\StudentsImport;
use App\Services\StudentService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{

    private StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Student::class);
        $query = Student::query()->visibleTo(auth()->user());

        $filteredQuery = (new StudentFilter($query, $request))->apply();

        $q = $this->applyFilters($filteredQuery, [
            'searchColumns' => ['full_name', 'identity'],
            'orderColumn' => 'created_at',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        $students = $query->withStandardRelations()->get();

        return $this->successWithPagination(
            StudentResource::collection($students),
            ['total' => $total, 'skip' => $q['skip'], 'limit' => $q['limit']],
            'success',
            200
        );
    }

    public function store(StoreStudentRequest $request)
    {
        $data = $request->validated();
        if ($data['identity']) {
            $student = Student::withTrashed()
                ->where('identity', $data['identity'])
                ->first();

            if ($student) {
                // إذا كان محذوف نرجعه
                if ($student->trashed()) {
                    $student->restore();
                }

                // نحدث البيانات
                $student->update($data);

                // Assign to halaqa if provided and not already assigned
                if (isset($data['halaqa_id']) && $data['halaqa_id']) {
                    $this->studentService->assignStudentToHalaqa($student, $data['halaqa_id']);
                }

                return $this->success(
                    new StudentResource($student),
                    'تم استعادة الطالب بنجاح',
                    201
                );
            }
        }
        try {
            $student = $this->studentService->create($request->validated());
        } catch (\InvalidArgumentException $th) {
            return $this->error($th->getMessage(), 422);
        }

        $student->load(Student::standardRelations());

        return $this->success(
            new StudentResource($student),
            'تم إنشاء الطالب بنجاح',
            201
        );
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);
        $student = $student->load(Student::standardRelations());


        return $this->success(
            new StudentResource($student),
            'success',
            200
        );
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        try {
            $student = $this->studentService->update($student, $request->validated());
        } catch (\InvalidArgumentException $th) {
            return $this->error($th->getMessage(), 422);
        }

        return $this->success(
            new StudentResource($student->load(Student::standardRelations())),
            'تم تحديث بيانات الطالب بنجاح'
        );
    }

    public function destroy(Student $student)
    {
        $this->authorize('delete', $student);
        $student->delete();

        return $this->success(
            null,
            'تم حذف الطالب بنجاح'
        );
    }

    /**
     * Import students from an Excel file.
     *
     * @param  \App\Http\Requests\Student\ImportStudentRequest  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function import(ImportStudentRequest $request)
    {
        $this->authorize('create', Student::class);
        try {
            Excel::import(
                new StudentsImport($request->except('file')),
                $request->file
            );
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success(
            null,
            'تم استيراد البيانات بنجاح'
        );
    }
}