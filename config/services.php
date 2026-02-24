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

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model' => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'iwinv_alimtalk' => [
        'endpoint' => 'https://alimtalk.bizservice.iwinv.kr/api/send/',
        'token' => env('IWINV_ALIMTALK_TOKEN', 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.IktON0lDODk2Wk9HUEZFVldMNE02MzkwMkZFNTFDN0Q2Ig.NaoTiZ-5P_1t6pO0pk476Sf7i0zaZh4p8iB8GRYPwy4'),
        'sender_number' => env('IWINV_SENDER_NUMBER', '1566-6779'),
    ],

    'iwinv_sms' => [
        'endpoint' => 'https://sms.bizservice.iwinv.kr/api/v2/send/',
        'api_key' => env('IWINV_SMS_API_KEY', 'OMFAIQE4DRT87GK0JUX64FEC54E72A23'),
        'auth_key' => env('IWINV_SMS_AUTH_KEY', '5c9bc78f61d41b46c456559833e5ce3d5545f0950752f46c114296972d96662f'),
        'sender_number' => env('IWINV_SENDER_NUMBER', '1566-6779'),
    ],

];
