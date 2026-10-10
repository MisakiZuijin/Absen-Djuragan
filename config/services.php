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

    'whatsapp' => [
        'url' => env('WHATSAPP_API_URL'),
    ],

    'external' => [
        'api_key' => env('EXTERNAL_API_KEY'),
    ],

    'intern_system' => [
        'url'     => env('INTERN_SYSTEM_URL', 'http://Internship_Management_System_SevenInc-develop.test'),
        'api_key' => env('INTERN_SYSTEM_API_KEY', env('EXTERNAL_API_KEY', 'djuragan_ext_140633f34edfaf504a3d6812c8c2485c')),
    ],
];
