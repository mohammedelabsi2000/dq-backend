<?php

namespace App\Services;

use App\Helpers\ConstantHelper;
use App\Models\HalaqaStudent;
use App\Models\PreviousAchievement;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class StudentService
{
    public function createOrUpdateStudent(array $data, User $requester = null): Student
    {
        // Check if student exists by identity
        $student = Student::withTrashed()->where('identity', $data['identity'])->first();

        if ($student) {
            // Update existing student
            $data = array_merge($data, $student->toArray());
            return $this->update($student, $data, $requester);
        } else {
            // Create new student
            $idQueryServices = new IdQueryServices();
            $personData = $idQueryServices->get($data['identity']);
            $studentData = $idQueryServices->mapping($personData);
            $data = array_merge($data, $studentData);
            return $this->create($data, $requester);
        }
    }

    public function create(array $data, User $requester = null): Student
    {
        return DB::transaction(function () use ($data, $requester) {

            $guardian = $this->findOrCreateGuardian($data['guardian_id'], $data['fName'] . ' ' . $data['sName'] . ' ' . $data['thName'] . ' ' . $data['family'], $requester);

            // Extract halaqa_id from data if present
            $halaqaId = $data['halaqa_id'] ?? null;
            unset($data['halaqa_id']);

            $student = Student::create($data);

            // Assign student to halaqa if provided
            if ($halaqaId) {
                $this->assignStudentToHalaqa($student, $halaqaId);
            }

            if ($requester) {
                $student->submitForApproval($requester);
            }

            return $student;
        });
    }

    public function update(Student $student, array $data, User $requester = null): Student
    {
        return DB::transaction(function () use ($student, $data, $requester) {

            if (isset($data['guardian_id'])) {
                $this->findOrCreateGuardian($data['guardian_id'], $student->fName . ' ' . $student->sName . ' ' . $student->thName . ' ' . $student->family, $requester);
            }

            // ✅ نتحقق إن كان halaqa_id موجوداً في الـ data (حتى لو null)
            $hasHalaqaKey = array_key_exists('halaqa_id', $data);
            $halaqaId = $data['halaqa_id'] ?? null;
            unset($data['halaqa_id']);

            $student->update($data);

            // ✅ ندخل هنا إن مُرِّر halaqa_id سواء كان null أو قيمة
            if ($hasHalaqaKey) {
                $this->updateStudentHalaqaAssignment($student, $halaqaId);
            }

            return $student;
        });
    }

    private function findOrCreateGuardian(string $identity, string $studentName, User $requester = null)
    {
        $guardian = User::withTrashed()->where('identity', $identity)->first();

        if (!$guardian) {
            $idQueryServices = new IdQueryServices();
            $personData = $idQueryServices->get($identity);
            $guardianData = $idQueryServices->mapping($personData);

            $guardian = User::create(array_merge([
                'name' => 'ولي أمر ' . $studentName,
                'email' => $identity . '@dq.com',
                'password' => Hash::make('12345678'),
                'identity' => $identity,
                'is_approved' => false,
                'is_active' => false,
            ], $guardianData));

            // Submit guardian for approval if requester is provided
            if ($requester) {
                $guardian->submitForApproval($requester);
            }
        } else {
            if ($guardian->trashed()) {
                $guardian->restore();
            }

            // If guardian exists but is not approved, submit for approval
            if (!$guardian->is_approved && $requester && !$guardian->approvalRequest) {
                $guardian->submitForApproval($requester);
            }
        }

        return $guardian;
    }

    private function updateOrCreatePreviousAchievement(Student $student, array $data): void
    {
        if (
            isset($data['memorized_juz_id']) ||
            isset($data['completed_juz_id']) ||
            isset($data['surah_id'])
        ) {
            PreviousAchievement::updateOrCreate([
                'student_id' => $student->id,
            ], [
                'memorized_juz_id' => $data['memorized_juz_id'] ?? null,
                'completed_juz_id' => $data['completed_juz_id'] ?? null,
                'surah_id' => $data['surah_id'] ?? null,
                'end_aya' => $data['end_aya'] ?? null,
            ]);
        }
    }


    public function assignStudentToHalaqa(Student $student, int $halaqaId): void
    {
        // Get the default enrollment status ID for "منتظم" (regular)
        $enrollmentStatusId = ConstantHelper::getConstantIdByName('enrollment_status', 'منتظم');

        HalaqaStudent::create([
            'halaqa_id' => $halaqaId,
            'student_id' => $student->id,
            'from_date' => now()->toDateString(),
            'enrollment_status_id' => $enrollmentStatusId,
        ]);
    }

    public function updateStudentHalaqaAssignment(Student $student, ?int $halaqaId): void
    {
        $activeEnrollment = HalaqaStudent::where('student_id', $student->id)
            ->whereNull('to_date')
            ->first();

        // لا يوجد تغيير فعلي
        if ($activeEnrollment && $activeEnrollment->halaqa_id === $halaqaId) {
            return;
        }

        // إغلاق التسجيل النشط الحالي (إن وجد) بدل حذفه، للحفاظ على السجل التاريخي
        if ($activeEnrollment) {
            $activeEnrollment->update(['to_date' => now()->toDateString()]);
        }

        // تنسيب للحلقة الجديدة إن تم تحديدها
        if ($halaqaId) {
            $this->assignStudentToHalaqa($student, $halaqaId);
        }
    }
}
