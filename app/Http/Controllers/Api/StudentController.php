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

        // Filter by branch (through halaqas or mosques)
        if (request()->filled('branch_id')) {
            $branchId = request()->integer('branch_id');
            $query->where(function ($q) use ($branchId) {
                // Students in halaqas under this branch
                $q->whereHas('halaqas', function ($hq) use ($branchId) {
                    $hq->where(function ($hqQuery) use ($branchId) {
                        // Halaqas under centers in this branch
                        $hqQuery->whereHasMorph('reference', ['center'], function ($centerQuery) use ($branchId) {
                            $centerQuery->whereHas('region', function ($regionQuery) use ($branchId) {
                                $regionQuery->where('branch_id', $branchId);
                            });
                        })
                            // Halaqas directly under regions in this branch
                            ->orWhereHasMorph('reference', ['region'], function ($regionQuery) use ($branchId) {
                                $regionQuery->where('branch_id', $branchId);
                            });
                    });
                })
                    // Students in mosques under this branch
                    ->orWhereHas('mosque', function ($mosqueQuery) use ($branchId) {
                        $mosqueQuery->whereHas('region', function ($regionQuery) use ($branchId) {
                            $regionQuery->where('branch_id', $branchId);
                        });
                    });
            });
        }

        // Filter by halaqa
        if (request()->filled('halaqa_id')) {
            $query->whereHas('halaqas', function ($q) {
                $q->where('halaqa_id', request()->integer('halaqa_id'));
            });
        }

        // Filter by center (through halaqas or mosques)
        if (request()->filled('center_id')) {
            $centerId = request()->integer('center_id');
            $query->where(function ($q) use ($centerId) {
                // Students in halaqas under this center
                $q->whereHas('halaqas', function ($hq) use ($centerId) {
                    $hq->whereHasMorph('reference', ['center'], function ($centerQuery) use ($centerId) {
                        $centerQuery->where('id', $centerId);
                    });
                })
                    // Students in mosques under this center
                    ->orWhereHas('mosque', function ($mosqueQuery) use ($centerId) {
                        $mosqueQuery->whereHas('centers', function ($centerQuery) use ($centerId) {
                            $centerQuery->where('id', $centerId);
                        });
                    });
            });
        }

        // Filter by region (through halaqas or mosques)
        if (request()->filled('region_id')) {
            $regionId = request()->integer('region_id');
            $query->where(function ($q) use ($regionId) {
                // Students in halaqas directly under this region
                // $q->whereHas('halaqas', function ($hq) use ($regionId) {
                //     $hq->whereHasMorph('reference', ['region'], function ($regionQuery) use ($regionId) {
                //         $regionQuery->where('id', $regionId);
                //     });
                // })
                //     // Students in halaqas under centers in this region
                //     ->orWhereHas('halaqas', function ($hq) use ($regionId) {
                //         $hq->whereHasMorph('reference', ['center'], function ($centerQuery) use ($regionId) {
                //             $centerQuery->where('region_id', $regionId);
                //         });
                //     })
                //     // Students whose mosque is in this region
                //     ->or
                $q->WhereHas('mosque', function ($mosqueQuery) use ($regionId) {
                    $mosqueQuery->where('region_id', $regionId);
                });
            });
        }

        // Filter by mosque
        if (request()->filled('mosque_id')) {
            $query->where('mosque_id', request()->integer('mosque_id'));
        }

        // Filter by gender
        if (request()->filled('gender')) {
            $query->where('gender', request()->input('gender'));
        }

        // Filter by marital status
        if (request()->filled('marital_status_id')) {
            $query->where('marital_status_id', request()->integer('marital_status_id'));
        }

        // Filter by money status
        if (request()->filled('money_status_id')) {
            $query->where('money_status_id', request()->integer('money_status_id'));
        }

        // Filter by guardian type
        if (request()->filled('guardian_type_id')) {
            $query->where('guardian_type_id', request()->integer('guardian_type_id'));
        }

        // Filter by age range
        if (request()->filled('age_min')) {
            $minAge = request()->integer('age_min');
            $query->where('dob', '<=', now()->subYears($minAge));
        }
        if (request()->filled('age_max')) {
            $maxAge = request()->integer('age_max');
            $query->where('dob', '>=', now()->subYears($maxAge + 1));
        }

        // Filter by enrollment status (active in halaqas)
        if (request()->boolean('has_active_halaqa')) {
            $query->whereHas('halaqas', function ($q) {
                $q->whereNull('halaqa_students.to_date')
                    ->orWhere('halaqa_students.to_date', '>=', now());
            });
        }

        // Filter by students with any halaqa
        if (request()->boolean('has_halaqa')) {
            $query->whereHas('halaqas');
        }

        // Filter by guardian
        if (request()->filled('guardian_id')) {
            $query->where('guardian_id', request()->input('guardian_id'));
        }

        $q = $this->applyFilters($query, [
            'searchColumns' => ['full_name', 'identity'],
            'orderColumn' => 'created_at',
        ]);

        $query = $q['query'];
        $total = $q['count'];

        $students = $query->withStandardRelations()->get();

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

        $student->load(Student::$standardRelations);

        return $this->success(
            new StudentResource($student),
            'تم إنشاء الطالب بنجاح',
            201
        );
    }

    public function show(Student $student)
    {
        $this->authorize('view', $student);
        $student = $student->load(Student::$standardRelations);

        return $this->success(
            new StudentResource($student),
            'success',
            200
        );
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        // $this->authorize('update', $student);
        try {
            $student = $this->studentService->update($student, $request->validated());
        } catch (\InvalidArgumentException $th) {
            return $this->error($th->getMessage(), 422);
        }

        return $this->success(
            new StudentResource($student->load(Student::$standardRelations)),
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