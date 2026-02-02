<?php
require 'c:/dometopia/new_admin/vendor/autoload.php';
$app = require_once 'c:/dometopia/new_admin/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$goodsSeq = 999999;
$optionSeq = 999999;
$goodsCode = time(); // goods_code is int(10) UNI

// Cleanup existing (partial) data
DB::table('fm_goods')->where('goods_seq', $goodsSeq)->delete();
DB::table('fm_goods_supply')->where('goods_seq', $goodsSeq)->delete();
DB::table('fm_scm_location_link')->where('goods_seq', $goodsSeq)->delete();

// 1. Create fm_goods
DB::table('fm_goods')->insert([
    'goods_seq' => $goodsSeq,
    'goods_name' => 'SCM Verification Test Product',
    'goods_code' => $goodsCode,
    'goods_view' => 'look', // Enum is look/notLook
    'tot_stock' => 100,
    'regist_date' => now(),
    'provider_seq' => 1,
    'shipping_policy' => 'shop',
    'runout_policy' => 'stock'
]);

// 2. Create fm_goods_supply
DB::table('fm_goods_supply')->insert([
    'supply_seq' => $goodsSeq, // Assuming supply_seq matches goods_seq for simplicity or auto-increment ignored
    'goods_seq' => $goodsSeq,
    'stock' => 100,
    // 'bad_stock' => 0, // Column might not exist or be named 'badstock', omitting to use default
    'option_seq' => $optionSeq
]);

// 3. Create fm_scm_location_link (wh_seq = 1)
DB::table('fm_scm_location_link')->insert([
    'goods_seq' => $goodsSeq,
    'wh_seq' => 1,
    'ea' => 100,
    'option_seq' => $optionSeq,
    'option_type' => 'option'
]);

// 4. Create dummy image for cart display
DB::table('fm_goods_image')->updateOrInsert(
    ['goods_seq' => $goodsSeq, 'image_type' => 'list1'],
    ['image' => '/data/goods/test.jpg', 'cut_number' => 1]
);

// 5. Create fm_goods_option (Required for cart)
DB::table('fm_goods_option')->insert([
    'option_seq' => $optionSeq,
    'goods_seq' => $goodsSeq,
    'price' => 1000,
    'consumer_price' => 1000,
    'option1' => 'Default'
]);


echo "Test Product Created: ID $goodsSeq with Option $optionSeq\n";
