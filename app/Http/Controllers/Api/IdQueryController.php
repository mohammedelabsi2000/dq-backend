<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\IdQueryServices;

class IdQueryController extends Controller
{

    private IdQueryServices $idQueryServices;

    public function __construct(IdQueryServices $idQueryServices)
    {
        $this->idQueryServices = $idQueryServices;
    }

    public function sendRequest(Request $request)
    {
        $identity = $request->id;

        $personData = null;

        try {
            $personData = $this->idQueryServices->get($identity);
        } catch (\InvalidArgumentException $e) {
            return $this->error($e->getMessage(), 422);
        }

        return $this->success([
            'identity' => $personData['CI_ID_NUM'],
            'fName' => $personData['CI_FIRST_ARB'],
            'sName' => $personData['CI_FATHER_ARB'],
            'thName' => $personData['CI_GRAND_FATHER_ARB'],
            'family' => $personData['CI_FAMILY_ARB'],
            'dob' => $personData['CI_BIRTH_DT'],
            'gender' => $personData['SEX'],
        ], 'success', 200);
    }
}
