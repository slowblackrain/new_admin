<?php

return [
    'toss' => [
        'client_key' => env('TOSS_CLIENT_KEY', 'test_ck_D5GePWvyJnrK0W0k6q8gLzN97Eoq'), // Default to test key found in legacy comments
        'secret_key' => env('TOSS_SECRET_KEY', 'test_sk_zXLkKEypNArWmo50nX3lmeaxYG5R'), // Default to test key found in legacy comments
        'mid' => env('TOSS_MID'),
    ],
    'pairing' => [
        'client_id' => env('PAIRING_CLIENT_ID', '23050362'), // Hardcoded ID from legacy cker.php
        'api_url' => env('PAIRING_API_URL', 'https://pairingpayments.com/extlink/receipt_tree.asp'),
    ],
    'active_pg' => env('ACTIVE_PG', 'toss'),
    'pairing_goods' => [
        64931, 67659, 16046, 9891, 192328, 205052, 204693
    ],
];
