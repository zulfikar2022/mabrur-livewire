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

    'is_for_department' => env('IS_FOR_DEPARTMENT', false),
    'default_role' => env('DEFAULT_ROLE', 'user'),

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'google' => [
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect' => env('GOOGLE_REDIRECT'),
    ],

    'imagekit' => [
        'public_key' => env('IMAGEKIT_PUBLIC_KEY'),
        'private_key' => env('IMAGEKIT_PRIVATE_KEY'),
        'url_endpoint' => env('IMAGEKIT_URL_ENDPOINT'),
    ],

    'courier_charge' => [
        'first_half_kg_isd' => env('FIRST_HALF_KG_ISD', 65),
        'first_kg_isd' => env('FIRST_KG_ISD', 75),
        'later_kgs_isd' => env('LATER_KGS_ISD', 20),

        'first_half_kg_osd' => env('FIRST_HALF_KG_OSD', 115),
        'first_kg_osd' => env('FIRST_KG_OSD', 135),
        'later_kgs_osd' => env('LATER_KGS_OSD', 20),
        'mango_delivery_charge_per_kg' => env('MANGO_DELIVERY_CHARGE_PER_KG', 15),
    ],

];
