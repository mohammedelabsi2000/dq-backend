<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiResponser;
use App\Http\Requests\HalaqaStudent\StoreHalaqaStudentRequest;
use App\Http\Requests\HalaqaStudent\UpdateHalaqaStudentRequest;
use App\Http\Resources\HalaqaStudentResource;
use App\Models\HalaqaStudent;

class HalaqaStudentController extends Controller
{
    use ApiResponser;

    /**
     * عرض جميع التسجيلات
     */
    public function index()
    {
        $query = HalaqaStudent::query();

        $q = $this->applyFilters($query, [
            'searchColumns' => ['id', 'student_id', 'halaqa_id'],
            'orderColumn' => 'created_at',
            'orderBy' => 'desc'
        ]);

        $query = $q['query'];
        $total = $q['count'];

        // حساب العدد الحقيقي بعد الفلترة
        // $total = (clone $query)->count();


        $data = $query->with([
            'halaqa',
            'halaqa.reference',
            'student',
            'enrollment_status'
        ])->get();

        return $this->apiResponse([
            'total' => $total,
            'skip' => $q['skip'],
            'limit' => $q['limit'],
            'data' => HalaqaStudentResource::collection($data),
        ], 'success', 200);
    }
    /**
     * عرض تسجيل محدد
     */
    public function show(HalaqaStudent $halaqaStudent)
    {
        $halaqaStudent = $halaqaStudent->load([
            'halaqa',
            'halaqa.reference',
            'student',
            'enrollment_status',
        ]);

        return $this->success(
            new HalaqaStudentResource($halaqaStudent),
            'success',
            200
        );
    }

    /**
     * إنشاء تسجيل جديد
     */
    public function store(StoreHalaqaStudentRequest $request)
    {
        $validated = $request->validated();

        $studentsCreated = [];
        $studentsAlreadyEnrolled = [];

        foreach ($validated['students'] as $studentId) {

            // التحقق هل الطالب مسجل في حلقة أخرى
            $existingEnrollment = HalaqaStudent::where('student_id', $studentId)
                ->whereNull('to_date')
                ->exists();

            if ($existingEnrollment) {
                $studentsAlreadyEnrolled[] = $studentId;
                continue;
            }

            $student = HalaqaStudent::create([
                'halaqa_id' => $validated['halaqa_id'],
                'student_id' => $studentId,
                'from_date' => $validated['from_date'],
                'enrollment_status_id' => $validated['enrollment_status_id'],
            ]);

            $studentsCreated[] = $student;
        }

        return $this->success(
            HalaqaStudentResource::collection(
                HalaqaStudent::with(['halaqa', 'student', 'enrollment_status'])
                    ->whereIn('id', collect($studentsCreated)->pluck('id'))
                    ->get()
            ),
            'تم تسجيل الطلاب في الحلقة بنجاح'
        );
    }
    /**
     * تعديل تسجيل موجود
     */
    public function update(
        UpdateHalaqaStudentRequest $request,
        HalaqaStudent $halaqaStudent
    ) {
        $validated = $request->validated();

        if ($halaqaStudent->halaqa_id != $validated['halaqa_id'] || $halaqaStudent->from_date != $validated['from_date']) {
            // اغلاق كل السجلات المفتوحة
            HalaqaStudent::where('student_id', $halaqaStudent->student_id)
                ->whereNull('to_date')
                ->update(['to_date' => $validated['from_date']]);

            $newRecord = HalaqaStudent::create([
                'student_id' => $halaqaStudent->student_id,
                'halaqa_id' => $validated['halaqa_id'],
                'from_date' => $validated['from_date'],
                'enrollment_status_id' => $validated['enrollment_status_id'] ?? $halaqaStudent->enrollment_status_id,
            ]);

            return $this->success(
                new HalaqaStudentResource($newRecord->load(['halaqa', 'student', 'enrollment_status'])),
                'تم نقل الطالب إلى الحلقة الجديدة بنجاح',
                201
            );
        }

        $halaqaStudent->update($validated);

        return $this->success(
            new HalaqaStudentResource($halaqaStudent->load(['halaqa', 'student', 'enrollment_status'])),
            'تم تحديث بيانات التسجيل بنجاح',
            200
        );
    }

    /**
     * حذف تسجيل
     */
    public function destroy(HalaqaStudent $halaqaStudent)
    {
        $halaqaStudent->delete();

        return $this->success(
            null,
            'تم حذف التسجيل بنجاح',
            202
        );
    }
}
