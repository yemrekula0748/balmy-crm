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

    'elektra_pdks' => [
        'foresta' => [
            'endpoint' => env('ELEKTRA_PDKS_FORESTA_ENDPOINT', 'https://3013.hoteladvisor.net'),
            'api_key' => env('ELEKTRA_PDKS_FORESTA_API_KEY'),
            'tenant_id' => env('ELEKTRA_PDKS_FORESTA_TENANT_ID', 32904),
            'company_id' => env('ELEKTRA_PDKS_FORESTA_FIRMA_ID', '3617'),
            'branch_id' => env('ELEKTRA_PDKS_FORESTA_BRANCH_ID', env('HOTELADVISOR_FORESTA_BRANCH_ID', 2)),
            'user_sync_tenant_id' => env('ELEKTRA_USER_SYNC_TENANT_ID', 32904),
            'user_sync_min_active' => env('ELEKTRA_USER_SYNC_MIN_ACTIVE', 25),
            'user_sync_min_ratio' => env('ELEKTRA_USER_SYNC_MIN_RATIO', 0.6),
            'department_aliases' => [
                'ÖNBÜRO' => 'Ön Büro',
                'KONSEPT & ANİMASYON' => 'Animasyon',
                'KAT HİZMETLERİ' => 'HK',
                'SATIŞ & PAZARLAMA' => 'Satış ve Pazarlama',
                'İDARİ İŞLER' => 'Güvenlik',
                'F&B BAR' => 'F&B',
                'F&B RESTAURANT' => 'F&B',
            ],
        ],
    ],

];
