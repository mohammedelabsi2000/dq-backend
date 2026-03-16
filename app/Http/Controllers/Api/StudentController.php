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
        $this->authorize('viewAny', Student::class);
        // $query = Student::query();
        $query = Student::query()->visibleTo(auth()->user());

        if (request()->filled('halaqa_id')) {
            // Students linked to the specified halaqa
            $query->whereHas('halaqas', function ($q) {
                $q->where('halaqa_id', request()->integer('halaqa_id'));
            });
        } elseif (request()->filled('center_id')) {
            // Students linked to the specified center through halaqas
            $query->whereHas('halaqas', function ($q) {
                $q->whereHasMorph(
                    'reference',
                    ['center'],
                    function ($q) {
                        $q->where('id', request()->integer('center_id'));
                    }
                );
            });
        } elseif (request()->filled('region_id')) {
            // Students linked to the specified region through halaqas or centers
            $regionId = request()->integer('region_id');
            $query->whereHas('halaqas', function ($q) use ($regionId) {
                $q->where(function ($q) use ($regionId) {
                    // Halaqas directly linked to the region
                    $q->whereHasMorph('reference', ['region'], function ($q) use ($regionId) {
                        $q->where('id', $regionId);
                    })
                        // Halaqas linked to centers that belong to the region
                        ->orWhereHasMorph('reference', ['center'], function ($q) use ($regionId) {
                            $q->where('region_id', $regionId);
                        });
                });
            });
        }

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
        $this->authorize('view', $student);
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
        // $this->authorize('update', $student);
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
        $this->authorize('delete', $student);
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
