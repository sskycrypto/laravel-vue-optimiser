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
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'yahoojp'  => [
        'api_endpoint'  => env('YAHOOJP_API_ENDPOINT'),
        'client_id'     => env('YAHOOJP_CLIENT_ID'),
        'client_secret' => env('YAHOOJP_CLIENT_SECRET'),
        'redirect'      => env('YAHOOJP_REDIRECT'),
    ],

    'yahoo'  => [
        'client_id'     => env('YAHOO_CLIENT_ID'),
        'client_secret' => env('YAHOO_CLIENT_SECRET'),
        'redirect'      => env('YAHOO_REDIRECT'),
    ],

    'outbrain' => [
        'api_endpoint' => env('OUTBRAIN_API_ENDPOINT'),
        'token_expires_days' => env('TOKEN_EXPIRES_DAYS'),
    ],

    'twitter'  => [
        'client_id'     => env('TWITTER_CLIENT_ID'),
        'client_secret' => env('TWITTER_CLIENT_SECRET'),
        'redirect'      => env('TWITTER_REDIRECT'),
    ],

    'taboola'  => [
        'api_endpoint'  => env('TABOOLA_API_ENDPOINT'),
        'client_id'     => env('TABOOLA_CLIENT_ID'),
        'client_secret' => env('TABOOLA_CLIENT_SECRET'),
        'redirect'      => env('TABOOLA_REDIRECT'),
    ],

    'tinify'  => [
        'api_key'  => env('TINIFY_API_KEY'),
    ],
];
