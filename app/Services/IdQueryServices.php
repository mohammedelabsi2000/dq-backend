<?php

namespace App\Services;

use App\Models\Student;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;

class IdQueryServices
{
    public function __construct()
    {
    }

    /**
     * @param string $id
     * @return array
     * @throws \InvalidArgumentException
     */
    public function get($id)
    {
        if (!ctype_digit($id) || strlen($id) != 9) {
            throw new \InvalidArgumentException('رقم الهوية يجب أن يكون 9 أرقام');
        }

        $idQueruAPI = config("thirdParty.id_query_api");

        $response = Http::timeout(60)
            ->asMultipart()
            ->post($idQueruAPI['base_url'], [
                [
                    'name' => 'id',
                    'contents' => $id,
                ],
                [
                    'name' => 'token',
                    'contents' => $idQueruAPI['token'],
                ],
            ]);

        $data = $response->json();

        $personData = null;

        if (isset($data['DATA'][0])) {
            $personData = $data['DATA'][0];
        }

        if (!is_array($personData) || !array_key_exists("CI_ID_NUM", $personData)) {
            throw new \InvalidArgumentException("لايوجد بيانات لرقم الهوية {$id}");
        }

        return $personData;
    }

    public function firstOrCreateUser(int $identity, array $data)
    {
        $user = User::withTrashed()->where('identity', $identity)->first();

        if ($user) {
            $user->update($data);
            return $user;
        }

        $personData = $this->get($identity);

        $userData = [
            'identity' => $identity,
            ...$this->mapping($personData),
            'email' => $identity . '@tahfiz.com',
            'password' => Hash::make('12345678'),
        ];

        $user = User::create(array_merge($userData, $data));
        return $user;
    }

    /* public function firstOrCreateStudent(int $identity, array $data)
    {
        $student = Student::withTrashed()->where('identity', $identity)->first();
        if ($student) {
            $student->update($data);
            return $student;
        }

        $student = Student::create(array_merge(['identity' => $identity], $data));
        return $student;
    } */

    /**
     * Transform the data from the ID query API to match the User model fields
     * @param array $data
     * @return array
     */
    private function mapping(array $data)
    {
        return [
            'fName' => $data['CI_FIRST_ARB'] ?? null,
            'sName' => $data['CI_FATHER_ARB'] ?? null,
            'thName' => $data['CI_GRAND_FATHER_ARB'] ?? null,
            'family' => $data['CI_FAMILY_ARB'] ?? null,
            'dob' => str_replace('/', '-', $data['CI_BIRTH_DT']) ?? null,
            'gender' => $data['SEX'] ?? null,
        ];
    }
}
