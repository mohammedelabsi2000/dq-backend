<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ImportStudentRequest;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Imports\StudentsImport;
use App\Services\StudentService;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{

    private StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function index()
    {
        $query = Student::query();

        $q = $this->applyFilters($query, [
            'searchColumns' => ['full_name', 'identity'],
            'orderColumn' => 'created_at',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        $students = $query->with([
            'mosque',
            'maritalStatus',
            'moneyStatus',
            'guardian',
            'guardianType',
            'prefixName',
        ])->get();

        return $this->apiResponse([
            'total' => $total,
            'skip' => $q['skip'],
            'limit' => $q['limit'],
            'data' => StudentResource::collection($students),
        ], 'success', 200);
    }

    public function store(StoreStudentRequest $request)
    {
        $data = $request->validated();

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

            return $this->success(
                new StudentResource($student),
                'تم استعادة الطالب بنجاح',
                201
            );
        }

        $student = $this->studentService->create($request->validated());
        $student->load([
            'mosque',
            'maritalStatus',
            'moneyStatus',
            'guardian',
            'guardianType',
            'prefixName',
        ]);

        return $this->success(
            new StudentResource($student),
            'تم إنشاء الطالب بنجاح',
            201
        );
    }

    public function show(Student $student)
    {
        $student = $student->load([
            'mosque',
            'maritalStatus',
            'moneyStatus',
            'guardian',
            'guardianType',
            'prefixName',
            'guardian',
        ]);

        return $this->success(
            new StudentResource($student),
            'success',
            200
        );
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $student = $this->studentService->update($student, $request->validated());

        return $this->success(
            new StudentResource($student->load([
            'mosque',
            'maritalStatus',
            'moneyStatus',
            'guardian',
            'guardianType',
            'prefixName',
            'guardian',
        ])),
            'تم تحديث بيانات الطالب بنجاح'
        );
    }

    public function destroy(Student $student)
    {
        $student->delete();

        return $this->success(
            null,
            'تم حذف الطالب بنجاح'
        );
    }

    public function import(ImportStudentRequest $request)
    {
        Excel::import(
            new StudentsImport($request->except('file')),
            $request->file
        );

        return $this->success(
            null,
            'تم استيراد البيانات بنجاح'
        );
    }
}
