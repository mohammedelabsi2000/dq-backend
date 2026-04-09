<?php
return [
    "quran_api" => [
        "base_url" => "https://api.alquran.cloud/v1",
    ],

    "id_query_api" => [
        "base_url" => "https://afp.daralquran.ps/api/id-query",
        "token" => env('ID_QUERY_TOKEN', 'z&G(FF=H\'~Wu#29yb<R=q{Rt,8X,&8kgcnFp<6M8Q)=AL7mr'),
        "master_column" => "CI_ID_NUM",
    ],
];
