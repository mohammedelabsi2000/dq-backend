<?php

namespace App\Http\Controllers\Api;

use App\Exports\FailedRowsExport;
use App\Exports\StudentExport;
use App\Filters\StudentFilter;
use App\Imports\Student\ValidateStudentsImport;
use App\Models\Student;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ImportStudentRequest;
use App\Http\Requests\Student\ImportStudentWithRelationRequest;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Imports\StudentsImport;
use App\Imports\Student\StudentWithRelationsImport;
use App\Services\StudentService;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
                // إذا كان الطالب موجوداً وغير محذوف نمنع الإضافة المكررة
                if (!$student->trashed()) {
                    return $this->error('الطالب موجود مسبقاً', 422);
                }

                // إذا كان محذوف نرجعه
                $student->restore();

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
            $student = $this->studentService->create($request->validated(), auth()->user());
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
            $student = $this->studentService->update($student, $request->validated(), auth()->user());
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

    public function import(ImportStudentRequest $request)
    {
        $this->authorize('create', Student::class);
        try {
            $importData = $request->except('file');
            $importData['user'] = auth()->user();
            Excel::import(
                new StudentsImport($importData),
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


    public function importWithRelations(Request $request)
    {
        $this->authorize('create', Student::class);

        // Validate the request to ensure a file is provided and is of the correct type
        $validator = Validator::make(
            $request->all(),
            [
                'file' => 'required|file|mimes:xlsx,xls',
            ],
            [
                'file.required' => 'الرجاء رفع ملف',
                'file.file' => 'المدخل يجب أن يكون ملف',
                'file.mimes' => 'يجب أن يكون الملف من نوع: xlsx أو xls',
            ]
        );

        if ($validator->fails()) {
            throw new HttpResponseException(
                $this->error(
                    'حدث خطأ في التحقق من البيانات',
                    422,
                    $validator->errors()
                )
            );
        }

        $import = new ValidateStudentsImport($request);
        Excel::import($import, $request->file);

        if (!empty($import->errors)) {
            return $this->error(
                'لم يتم استيراد البيانات بسبب وجود أخطاء في بعض الصفوف. يرجى مراجعة الأخطاء وتصحيحها ثم إعادة المحاولة.',
                422,
                $import->errors
            );
        }


        try {
            $userId = auth()->id();
            $filePath = $request->file('file')->storeAs('imports', 'students-with-relations-' . now()->timestamp . '.xlsx', 'public');
            $filePath = public_path('storage/' . $filePath);
            (new StudentWithRelationsImport($userId))->queue($filePath);
        } catch (\Throwable $th) {
            return $this->error($th->getMessage(), 422);
        }

        return $this->success(
            null,
            'تم استيراد البيانات بنجاح'
        );
    }

    public function export()
    {
        return Excel::download(new StudentExport, 'students.xlsx');
    }
}
