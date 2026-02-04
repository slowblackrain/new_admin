<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Charge Emoney
DB::table('fm_member')->where('userid', 'newjjang3')->update(['emoney' => 1000000, 'cash' => 1000000]);
echo "Charged Emoney for newjjang3.\n";

// 2. Create Test ATS Goods
// Check if exists
$exists = DB::table('fm_category_link')
    ->where('category_code', 'like', '0159%')
    ->exists();

if (!$exists) {
    // Create Dummy Goods
    $goodsId = DB::table('fm_goods')->insertGetId([
        'goods_name' => 'ATS Test Product (Auto-Copy)',
        'goods_code' => 'ATS_TEST_001',
        'goods_view' => 'look',
        'goods_status' => 'normal',
        'provider_seq' => 1, // SCM
        'regist_date' => now(),
        'update_date' => now(),
        'price' => 10000,
        'consumer_price' => 10000,
        'supply_price' => 8000,
        'tot_stock' => 100,
        // ... essential fields
        'runout_policy' => 'unlimited',
        'shipping_policy' => 'shop',
        'goods_type' => 'goods'
    ]);
    
    // Create Option
    $optSeq = DB::table('fm_goods_option')->insertGetId([
        'goods_seq' => $goodsId,
        'price' => 10000,
        'consumer_price' => 10000,
        'supply_price' => 8000,
        'provider_price' => 7000, // ATS Supply Price
        'stock' => 100,
        'option_type' => 'T',
        'default_option' => 'y'
    ]);

    DB::table('fm_goods_supply')->insert([
        'goods_seq' => $goodsId,
        'option_seq' => $optSeq,
        'stock' => 100,
        'supply_price' => 8000
    ]);
    
    // Link Category (0159 - ATS)
    DB::table('fm_category_link')->insert([
        'goods_seq' => $goodsId,
        'category_code' => '0159001',
        'link' => 1
    ]);
    
    echo "Created ATS Goods: $goodsId\n";
} else {
    echo "ATS Goods already exists.\n";
    // Force set one to Look/Normal
    $link = DB::table('fm_category_link')->where('category_code', 'like', '0159%')->first();
    DB::table('fm_goods')->where('goods_seq', $link->goods_seq)->update([
        'goods_view' => 'look',
        'goods_status' => 'normal',
        'tot_stock' => 9999,
        'provider_seq' => 1
    ]);
    echo "Updated existing ATS Goods: " . $link->goods_seq . "\n";
}
