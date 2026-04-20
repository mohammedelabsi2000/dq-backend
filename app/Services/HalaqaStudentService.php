<?php

namespace App\Services;

use App\Models\HalaqaStudent;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HalaqaStudentService
{
    /**
     * التحقق من تغيير الحلقة أو تاريخ البداية
     * 
     * @param HalaqaStudent $halaqaStudent
     * @param array $validated
     * @return bool
     */
    public function hasHalaqaOrFromDateChanged(HalaqaStudent $halaqaStudent, array $validated): bool
    {
        $halaqaChanged = isset($validated['halaqa_id']) &&
            $halaqaStudent->halaqa_id != $validated['halaqa_id'];

        $fromDateChanged = isset($validated['from_date']) &&
            $halaqaStudent->from_date->toDateString() != $validated['from_date'];

        return $halaqaChanged || $fromDateChanged;
    }

    /**
     * معالجة تحديث تسجيل الطالب في الحلقة
     * إذا تغيرت الحلقة أو تاريخ البداية → ننقل الطالب (نغلق السجل القديم وننشئ جديد)
     * وإلا → تحديث مباشر للسجل الحالي
     * 
     * @param HalaqaStudent $halaqaStudent
     * @param array $validated
     * @return HalaqaStudent
     */
    public function updateHalaqaStudentEnrollment(HalaqaStudent $halaqaStudent, array $validated): HalaqaStudent
    {
        if ($this->hasHalaqaOrFromDateChanged($halaqaStudent, $validated)) {
            return $this->transferStudent($halaqaStudent, $validated);
        }

        return $this->updateEnrollmentDetails($halaqaStudent, $validated);
    }

    /**
     * نقل الطالب إلى حلقة جديدة
     * 1. إغلاق جميع السجلات المفتوحة (where to_date is null)
     * 2. إنشاء سجل جديد بالحلقة والتاريخ الجديد
     * 
     * @param HalaqaStudent $halaqaStudent
     * @param array $validated
     * @return HalaqaStudent
     */
    private function transferStudent(HalaqaStudent $halaqaStudent, array $validated): HalaqaStudent
    {
        return DB::transaction(function () use ($halaqaStudent, $validated) {
            // إغلاق كل السجلات المفتوحة بتاريخ من_البداية الجديد
            HalaqaStudent::where('student_id', $halaqaStudent->student_id)
                ->whereNull('to_date')
                ->update(['to_date' => $validated['from_date']]);

            // إنشاء سجل جديد
            $newRecord = HalaqaStudent::create([
                'student_id' => $halaqaStudent->student_id,
                'halaqa_id' => $validated['halaqa_id'] ?? $halaqaStudent->halaqa_id,
                'from_date' => $validated['from_date'] ?? $halaqaStudent->from_date,
                'to_date' => $validated['to_date'] ?? null,
                'enrollment_status_id' => $validated['enrollment_status_id'] ?? $halaqaStudent->enrollment_status_id,
            ]);

            // حذف السجل القديم إذا لم يعد مستخدماً
            if ($halaqaStudent->exists()) {
                $halaqaStudent->delete();
            }

            return $newRecord;
        });
    }

    /**
     * تحديث مباشر لتفاصيل التسجيل (to_date, enrollment_status_id, etc)
     * يُستخدم عندما لا تتغير الحلقة أو تاريخ البداية
     * 
     * @param HalaqaStudent $halaqaStudent
     * @param array $validated
     * @return HalaqaStudent
     */
    private function updateEnrollmentDetails(HalaqaStudent $halaqaStudent, array $validated): HalaqaStudent
    {
        $halaqaStudent->update($validated);
        return $halaqaStudent->refresh();
    }

    /**
     * التحقق من تسجيل الطالب في حلقة نشطة
     * 
     * @param int $studentId
     * @return bool
     */
    public function isStudentEnrolledInActiveHalaqa(int $studentId): bool
    {
        return HalaqaStudent::where('student_id', $studentId)
            ->whereNull('to_date')
            ->exists();
    }
}
