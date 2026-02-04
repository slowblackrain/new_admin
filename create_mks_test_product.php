<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Create MKS Product
$existing = DB::table('fm_goods')->where('goods_scode', 'MKS-001')->first();
if ($existing) {
    $goodsSeq = $existing->goods_seq;
    echo "Using existing product: $goodsSeq\n";
} else {
    $goodsSeq = DB::table('fm_goods')->insertGetId([
        'goods_name' => 'MKS Test Product',
        'goods_code' => (string) rand(1000000, 9999999), // Numeric string to avoid cast to 0
        'goods_scode' => 'MKS-001',
        'goods_view' => 'look',
        'goods_status' => 'normal',
        'provider_status' => '1',
        'regist_date' => now(),
        'update_date' => now(),
        'provider_seq' => 1,
        'tax' => 'tax',
        'shipping_policy' => 'shop',
        'min_purchase_limit' => 'unlimit',
        'max_purchase_limit' => 'unlimit',
        'option_use' => '0',
        'goods_type' => 'goods'
    ]);
    echo "Created MKS Test Product: $goodsSeq\n";
}

// 2. Link to Category 0110
DB::table('fm_category_link')->insert([
    'goods_seq' => $goodsSeq,
    'category_code' => '0110',
    'link' => 1
]);

// 3. Add default option (required for price)
DB::table('fm_goods_option')->insert([
    'goods_seq' => $goodsSeq,
    'default_option' => 'y',
    'price' => 10000,
    'consumer_price' => 12000,
    'provider_price' => 8000,
    'option1' => ''
]);

echo "Created MKS Test Product: $goodsSeq\n";
