<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\HalaqaStudent\StoreHalaqaStudentRequest;
use App\Http\Requests\HalaqaStudent\UpdateHalaqaStudentRequest;
use App\Models\HalaqaStudent;
use Illuminate\Http\JsonResponse;

class HalaqaStudentController extends Controller
{
    /**
     * عرض جميع التسجيلات
     */
    public function index(): JsonResponse
    {
        $students = HalaqaStudent::with(['halaqa', 'student', 'status'])->get();
        return response()->json($students);
    }

    /**
     * عرض تسجيل محدد
     */
    public function show($id): JsonResponse
    {
        $student = HalaqaStudent::with(['halaqa', 'student', 'status'])->find($id);

        if (!$student) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        return response()->json($student);
    }

    /**
     * إنشاء تسجيل جديد
     */
    public function store(StoreHalaqaStudentRequest $request): JsonResponse
    {
        $student = HalaqaStudent::create($request->validated());
        return response()->json($student, 201);
    }

    /**
     * تعديل تسجيل موجود
     */
    public function update(UpdateHalaqaStudentRequest $request, $id): JsonResponse
    {
        $student = HalaqaStudent::find($id);

        if (!$student) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $student->update($request->validated());

        return response()->json($student);
    }

    /**
     * حذف تسجيل
     */
    public function destroy($id): JsonResponse
    {
        $student = HalaqaStudent::find($id);

        if (!$student) {
            return response()->json(['message' => 'Record not found'], 404);
        }

        $student->delete();

        return response()->json(['message' => 'Record deleted successfully']);
    }
}