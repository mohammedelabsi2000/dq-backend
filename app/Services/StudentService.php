<?php

namespace App\Services;

use App\Models\PreviousAchievement;
use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class StudentService
{
    use ApiResponser;
    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {

            $guardian = $this->findOrCreateGuardian($data['guardian_id'], $data['fName']);

            // Extract halaqa_id from data if present
            $halaqaId = $data['halaqa_id'] ?? null;
            unset($data['halaqa_id']);

            $student = Student::create($data);
            $this->updateOrCreatePreviousAchievement($student, $data);

            // Assign student to halaqa if provided
            if ($halaqaId) {
                $this->assignStudentToHalaqa($student, $halaqaId);
            }

            return $student;
        });
    }

    public function update(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {

            if (isset($data['guardian_id'])) {
                $this->findOrCreateGuardian($data['guardian_id'], $student->fName);
            }

            // Extract halaqa_id from data if present
            $halaqaId = $data['halaqa_id'] ?? null;
            unset($data['halaqa_id']);

            $student->update($data);
            $this->updateOrCreatePreviousAchievement($student, $data);

            // Handle halaqa assignment if provided
            if ($halaqaId !== null) {
                $this->updateStudentHalaqaAssignment($student, $halaqaId);
            }

            return $student;
        });
    }

    private function findOrCreateGuardian(string $identity, string $studentName)
    {
        $guardian = User::withTrashed()->where('identity', $identity)->first();

        if (!$guardian) {
            $idQueryController = new IdQueryController();
            $data = $idQueryController->getDataFromAPI($identity);
            $gurdianData = [];

            try {
                $data['CI_ID_NUM'];
            } catch (\Throwable $th) {
                return $this->notFound("لايوجد بيانات لرقم الهوية {$identity}");
            }

            $gurdianData = [
                'fName' => $data['CI_FIRST_ARB'] ?? null,
                'sName' => $data['CI_FATHER_ARB'] ?? null,
                'thName' => $data['CI_GRAND_FATHER_ARB'] ?? null,
                'family' => $data['CI_FAMILY_ARB'] ?? null,
                'dob' => $data['CI_BIRTH_DT'] ?? null,
                'gender' => $data['SEX'] ?? null,
            ];

            $guardian = User::create(array_merge([
                'name' => 'ولي أمر ' . $studentName,
                'email' => $identity . '@dq.com',
                'password' => Hash::make('12345678'),
                'identity' => $identity,
            ], $gurdianData));
        } else {
            if ($guardian->trashed()) {
                $guardian->restore();
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
        // Remove existing halaqa assignments
        HalaqaStudent::where('student_id', $student->id)->delete();

        // Assign to new halaqa if provided
        if ($halaqaId) {
            $this->assignStudentToHalaqa($student, $halaqaId);
        }
    }
}
