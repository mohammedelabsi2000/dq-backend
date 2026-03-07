<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiTestController extends Controller
{
    //

    public function test()
    {
        $response = Http::asMultipart()->post('https://afp.daralquran.ps/api/id-query/', [
            'id' => '801109042',
            'token' => 'z&G(FF=H\'~Wu#29yb<R=q{Rt,8X,&8kgcnFp<6M8Q)=AL7mr'
        ]);

        return $response->body();
    }
}
