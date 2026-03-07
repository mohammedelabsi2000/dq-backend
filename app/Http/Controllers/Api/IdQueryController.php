<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Constant;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class IdQueryController extends Controller
{
    public function sendRequest(Request $request)
    {

        $apiURL = 'https://afp.daralquran.ps/api/id-query/';

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $apiURL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 60,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'id' => $request->id,
                'token' => 'z&G(FF=H\'~Wu#29yb<R=q{Rt,8X,&8kgcnFp<6M8Q)=AL7mr'
            ),
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        try {
            $data = json_decode($response, true)['DATA'][0];
        } catch (\Throwable $th) {
            return $this->notFound($th->getMessage());
        }

        return $this->apiResponse([
                // 'data' => $data,
                'identity ' => $data['CI_ID_NUM'],
                'fName' => $data['CI_FIRST_ARB'],
                'sName' => $data['CI_FATHER_ARB'],
                'thName' => $data['CI_GRAND_FATHER_ARB'],
                'family' => $data['CI_FAMILY_ARB'],
                'dob' => $data['CI_BIRTH_DT'],
                'gender' => $data['SEX'],
                // 'marital_status_id' => Constant::where('name', 'LIKE', $data['SOCIAL_STATUS'])->get('id'),
        ], 'success', 200);
    }
}
