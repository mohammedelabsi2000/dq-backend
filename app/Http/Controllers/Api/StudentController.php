<?php

namespace App\Http\Controllers\Api;

use App\Models\Student;
use App\Http\Controllers\Controller;
use App\Http\Requests\Student\StoreStudentRequest;
use App\Http\Requests\Student\UpdateStudentRequest;
use App\Models\User;
use App\Services\StudentService;
use Illuminate\Support\Facades\Hash;

class StudentController extends Controller
{
    private StudentService $studentService;

    public function __construct(StudentService $studentService)
    {
        $this->studentService = $studentService;
    }
    public function index()
    {
        return Student::with([
            'mosque',
            'maritalStatus',
            'moneyStatus',
            'guardianType',
            'prefixName'
        ])->paginate();
    }

    public function store(StoreStudentRequest $request)
    {
        $student = $this->studentService->create($request->validated());

        return response()->json($student, 201);
    }

    public function show(Student $student)
    {
        return $student->load([
            'mosque',
            'maritalStatus',
            'moneyStatus',
            'guardianType',
            'prefixName'
        ]);
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