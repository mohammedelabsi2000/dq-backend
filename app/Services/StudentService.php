<?php

namespace App\Services;

use App\Helpers\ConstantHelper;
use App\Http\Controllers\Api\IdQueryController;
use App\Http\Traits\ApiResponser;
use App\Models\HalaqaStudent;
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
