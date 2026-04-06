<?php

namespace App\Services;

use App\Http\Controllers\Api\IdQueryController;
use App\Http\Traits\ApiResponser;
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
}