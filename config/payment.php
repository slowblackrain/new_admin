<?php

return [
    'toss' => [
        // R (General)
        'r_client_key' => env('TOSS_PAYMENTS_R_CLIENT_ID'),
        'r_secret_key' => env('TOSS_PAYMENTS_R_SECRET_KEY'),
        
        // M (Emoney Recharge specific, if Toss is used instead of Pairing)
        'm_client_key' => env('TOSS_PAYMENTS_M_CLIENT_ID'),
        'm_secret_key' => env('TOSS_PAYMENTS_M_SECRET_KEY'),
        
        // Test Keys (Fallback)
        'test_client_key' => env('TOSS_PAYMENTS_TR_CLIENT_ID', 'test_ck_Z1aOwX7K8meR6L0pQQQ8yQxzvNPG'),
        'test_secret_key' => env('TOSS_PAYMENTS_TR_SECRET_KEY', 'test_sk_5OWRapdA8dYBo2Oz7lP3o1zEqZKL'),
        
        'is_test_mode' => env('TOSS_PAYMENTS_TEST_MODE', false),
    ],
    'pairing' => [
        'client_id' => env('PAIRING_CLIENT_ID', '23050362'), 
        'test_client_id' => env('PAIRING_TEST_CLIENT_ID', '23049615'),
        'api_url' => env('PAIRING_API_URL', 'https://pairingpayments.com/extlink/receipt_tree.asp'),
        'is_test_mode' => env('PAIRING_TEST_MODE', false),
    ],
    // The goods_seq specific for Point/Emoney charging, triggering Pairing (cker) PG
    'pairing_goods' => [
        9891, 16046, 10327, 64931, 64972, 67659, 80122, 80103, 80100, 80053, 80050, 80041, 96536, 192328, 205052, 204693, 64108, 195370, 195371, 195372
    ],
];
