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
    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {

            $guardian = $this->findOrCreateGuardian($data['guardian_id'], $data['fName']);

            // Extract halaqa_id from data if present
            $halaqaId = $data['halaqa_id'] ?? null;
            unset($data['halaqa_id']);

            $student = Student::create($data);

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
            $idQueryServices = new IdQueryServices();
            $personData = null;
            $gurdianData = [];

            $personData = $idQueryServices->get($identity);
            /* try {
            } catch (\InvalidArgumentException $e) {
                return $this->notFound("لايوجد بيانات لرقم هوية ولي الأمر {$identity}");
            } */

            $gurdianData = [
                'fName' => $personData['CI_FIRST_ARB'] ?? null,
                'sName' => $personData['CI_FATHER_ARB'] ?? null,
                'thName' => $personData['CI_GRAND_FATHER_ARB'] ?? null,
                'family' => $personData['CI_FAMILY_ARB'] ?? null,
                'dob' => $personData['CI_BIRTH_DT'] ?? null,
                'gender' => $personData['SEX'] ?? null,
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