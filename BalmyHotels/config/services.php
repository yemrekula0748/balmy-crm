<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'tripadvisor' => [
        'key' => env('TRIPADVISOR_API_KEY'),
    ],

    'google_places' => [
        'key' => env('GOOGLE_PLACES_API_KEY'),
    ],

    'hoteladvisor' => [
        'base_url' => env('HOTELADVISOR_BASE_URL', 'https://4001.hoteladvisor.net'),
        'hotels' => [
            'foresta' => [
                'name' => env('HOTELADVISOR_FORESTA_NAME', 'Balmy Foresta'),
                'hotel_id' => env('HOTELADVISOR_FORESTA_HOTEL_ID', 32904),
                'branch_id' => env('HOTELADVISOR_FORESTA_BRANCH_ID', 2),
                'api_key' => env('HOTELADVISOR_FORESTA_API_KEY'),
            ],
            'beach' => [
                'name' => env('HOTELADVISOR_BEACH_NAME', 'Balmy Beach Resort'),
                'hotel_id' => env('HOTELADVISOR_BEACH_HOTEL_ID', 30570),
                'branch_id' => env('HOTELADVISOR_BEACH_BRANCH_ID', 1),
                'api_key' => env('HOTELADVISOR_BEACH_API_KEY'),
            ],
        ],
    ],

];
