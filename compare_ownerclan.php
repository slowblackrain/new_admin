<?php

require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

$recentCode = 'WFP8ZWS'; // 아쿠아 물총(스카이)
$oldCode = 'WFOSRVM';   // 종이 방향제 10gX12개입(라벤더)

$authUrlRow = DB::table('fm_config')
    ->where('groupcd', 'ownerclan')
    ->where('codecd', 'pro_url')
    ->first();
    
$venderRow = DB::table('fm_config')
    ->where('groupcd', 'ownerclan')
    ->where('codecd', 'vender')
    ->first();

$authUrl = $authUrlRow ? $authUrlRow->value : 'https://auth.ownerclan.com/auth';
$venderConfig = $venderRow ? json_decode($venderRow->value, true) : null;

if (!$venderConfig) {
    die("Vendor config not found\n");
}

$response = Http::withHeaders([
    'Content-Type' => 'application/json',
])->withoutVerifying()->post($authUrl, $venderConfig);

if (!$response->successful()) {
    die("Auth failed: " . $response->body() . "\n");
}

$jwtToken = trim($response->body());

$apiUrl = 'https://api.ownerclan.com/v1/graphql';

function fetchOwnerclanProduct($code, $token, $apiUrl) {
    $query = <<<'GRAPHQL'
query ($key: ID!) {
  item(key: $key) {
    key
    name
    category {
      id
    }
    options {
      price
    }
    origin
    production
    metadata
  }
}
GRAPHQL;

    $res = Http::withHeaders([
        'Authorization' => 'Bearer ' . $token,
        'Content-Type' => 'application/json',
    ])->withoutVerifying()->timeout(10)->post($apiUrl, [
        'query' => $query,
        'variables' => ['key' => $code],
    ]);

    return $res->json();
}

$recentData = fetchOwnerclanProduct($recentCode, $jwtToken, $apiUrl);
$oldData = fetchOwnerclanProduct($oldCode, $jwtToken, $apiUrl);

file_put_contents('ownerclan_comparison.json', json_encode([
    'recent' => $recentData,
    'old' => $oldData,
], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

echo "Comparison saved to ownerclan_comparison.json\n";
