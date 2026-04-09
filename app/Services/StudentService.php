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
    public function create(array $data): Student
    {
        return DB::transaction(function () use ($data) {

            $guardian = $this->findOrCreateGuardian($data['guardian_id'], $data['fName']);
            $student = Student::create($data);
            $this->updateOrCreatePreviousAchievement($student, $data);
            return $student;
        });
    }

    public function update(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {

            if (isset($data['guardian_id'])) {
                $this->findOrCreateGuardian($data['guardian_id'], $student->fName);
            }

            $student->update($data);
            $this->updateOrCreatePreviousAchievement($student, $data);

            return $student;
        });
    }

    private function findOrCreateGuardian(string $identity, string $studentName): User
    {
        $guardian = User::withTrashed()->where('identity', $identity)->first();

        if (!$guardian) {
            $guardian = User::create([
                'name' => 'ولي أمر ' . $studentName,
                'email' => $identity . '@dq.com',
                'password' => Hash::make('12345678'),
                'identity' => $identity,
            ]);
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
}
