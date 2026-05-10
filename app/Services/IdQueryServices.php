<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class IdQueryServices
{
    public function __construct() {}

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
}
