<?php
return [
    "quran_api" => [
        "base_url" => "https://api.quran.com/api/v4",
    ],

    "id_query_api" => [
        "base_url" => "https://afp.daralquran.ps/api/id-query",
        "token" => env('ID_QUERY_TOKEN'),
        "master_column" => "CI_ID_NUM",
    ],

    "areas_api" => [
        "base_url" => env('AREAS_API_URL', 'https://afp.daralquran.ps/api/v2/external'),
        "api_key" => env('AREAS_API_KEY'),
    ],
];
