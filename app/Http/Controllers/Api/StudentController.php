<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Http\Resources\StudentResource;
use App\Http\Traits\ApiResponser;
use App\Models\User;
use App\Services\StudentService;

class StudentController extends Controller
{
    use ApiResponser;
    private StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }

    public function index()
    {
        $students =  Student::with([
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
}