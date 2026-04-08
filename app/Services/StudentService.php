<?php

namespace App\Services;

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

            return Student::create($data);
        });
    }

    public function update(Student $student, array $data): Student
    {
        return DB::transaction(function () use ($student, $data) {

            if (isset($data['guardian_id'])) {
                $this->findOrCreateGuardian($data['guardian_id'], $student->fName);
            }

            $student->update($data);

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
}