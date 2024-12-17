<?php

return [
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

    'gemini' => [
    'api_key' => env('GEMINI_API_KEY'),
],

'serper' => [
    'key' => env('SERPER_API_KEY'),
],

  'google' => [
    'places_api_key' => env('GOOGLE_PLACES_API_KEY'),
    'maps_api_key' => env('GOOGLE_MAPS_API_KEY'),
    'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT_URI'),
        'application_name' => env('GOOGLE_APPLICATION_NAME'),
    'gmb_scope' => [
        'https://www.googleapis.com/auth/business.manage',
        'https://www.googleapis.com/auth/userinfo.email',
        'https://www.googleapis.com/auth/userinfo.profile'
    ]
],
'rate_limit' => [
    'max_requests_per_minute' => 60,
    'retry_attempts' => 3,
    'retry_delay' => 60
]


];
