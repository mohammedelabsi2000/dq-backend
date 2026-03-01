<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\ImportStudentRequest;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Http\Traits\ApiResponser;
use App\Imports\StudentsImport;
use App\Models\User;
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
        $students = Student::with([
            'mosque',
            'maritalStatus',
            'moneyStatus',
            'guardianType',
            'prefixName'
        ])->get();


        return $this->apiResponse(
            StudentResource::collection($students),
            'success',
            200
        );
    }

    public function store(StoreStudentRequest $request)
    {
        $student = $this->studentService->create($request->validated());

        return response()->json($student, 201);
    }

    public function show(Student $student)
    {
        $student = $student->load([
            'mosque',
            'maritalStatus',
            'moneyStatus',
            'guardianType',
            'prefixName',
            'guardian',
        ]);

        return $this->apiResponse(
            new StudentResource($student),
            'success',
            200
        );
    }

    public function update(UpdateStudentRequest $request, Student $student)
    {
        $student = $this->studentService->update($student, $request->validated());

        return response()->json($student);
    }

    public function destroy(Student $student)
    {
        $student->delete();
        return response()->json(['message' => 'Deleted successfully']);
    }

    public function import(ImportStudentRequest $request)
    {

        Excel::import(

            new StudentsImport(
                $request->except('file')
            ),
            $request->file
        );

        return response()->json(['message' => 'Imported successfully']);
    }
}