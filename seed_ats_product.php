<?php

use App\Models\Seller;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// 1. Ensure newjjang3 seller exists
$providerId = 'newjjang3';
$seller = Seller::where('provider_id', $providerId)->first();

if (!$seller) {
    echo "Creating seller $providerId...\n";
    // Check member
    $member = DB::table('fm_member')->where('userid', $providerId)->first();
    $memberSeq = $member ? $member->member_seq : DB::table('fm_member')->insertGetId([
        'userid' => $providerId,
        'user_name' => 'New Jjang 3',
        'status' => 'active',
        'regist_date' => now(),
        'update_date' => now(),
    ]);

    $providerSeq = DB::table('fm_provider')->insertGetId([
        'userid' => $providerId,
        'provider_id' => $providerId,
        'provider_name' => 'New Jjang Provider',
        'provider_status' => 'Y',
        'regdate' => now(),
    ]);
    
    // We need to re-fetch to get model or just assume it works for the controller fallback
} else {
    echo "Seller $providerId exists.\n";
    $providerSeq = $seller->provider_seq;
    $member = DB::table('fm_member')->where('userid', $providerId)->first();
    $memberSeq = $member->member_seq;
}

// 2. Create an ATS Product (Category 0159)
$goodsSeq = DB::table('fm_goods')->insertGetId([
    'goods_name' => 'Test ATS Product ' . rand(100, 999),
    'goods_code' => rand(100000, 999999), // Temp int
    'goods_scode' => 'ATS' . rand(1000, 9999),
    'provider_seq' => $providerSeq,
    'provider_member_seq' => $memberSeq, // Owner
    'goods_status' => 'normal',
    'goods_view' => 'look',
    'regist_date' => now(),
    'update_date' => now(),
    'goods_type' => 'goods',
    'goods_kind' => 'goods',
    // 'consumer_price' => 30000, 
    // 'price' => 25000,
    // Add other non-nulls if strictly required, but many have defaults
]);

// 3. Link to Category 0159 (Agency)
DB::table('fm_category_link')->insert([
    'goods_seq' => $goodsSeq,
    'category_code' => '01590001', // Example agency category
    'link' => 1
]);

// 4. Add Option
$optionSeq = DB::table('fm_goods_option')->insertGetId([
    'goods_seq' => $goodsSeq,
    'consumer_price' => 30000,
    'price' => 25000,
    // 'supply_price' => 15000, // Legacy sometimes puts it here? No, fm_goods_supply
]);

// 5. Add Supply
DB::table('fm_goods_supply')->insert([
    'option_seq' => $optionSeq,
    'supply_price' => 15000,
    'stock' => 100
]);

echo "Created ATS Product: Seq=$goodsSeq\n";
