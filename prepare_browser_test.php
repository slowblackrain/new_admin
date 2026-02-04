<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Ensure Category 0110 exists
$cat = DB::table('fm_category')->where('category_code', '0110')->first();
if (!$cat) {
    DB::table('fm_category')->insert([
        'category_code' => '0110',
        'title' => 'Browser Verify Category',
        'parent_id' => 1,
        'level' => 2,
        'hide' => '0',
        'regist_date' => now(),
        'update_date' => now()
    ]);
    echo "Created Category 0110.\n";
} else {
    echo "Category 0110 exists.\n";
}

// 2. Create MKS Product
$goodsScode = 'MKS-BROWSER-TEST';
$existing = DB::table('fm_goods')->where('goods_scode', $goodsScode)->first();

if (!$existing) {
    $goodsSeq = DB::table('fm_goods')->insertGetId([
        'goods_name' => 'MKS Invisible Product',
        'goods_code' => (string) rand(1000000, 9999999), 
        'goods_scode' => $goodsScode,
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
    
    // Link to Category
    DB::table('fm_category_link')->insert([
        'goods_seq' => $goodsSeq,
        'category_code' => '0110',
        'link' => 1
    ]);
    
    // Default Option
    DB::table('fm_goods_option')->insert([
        'goods_seq' => $goodsSeq,
        'default_option' => 'y',
        'price' => 50000,
        'consumer_price' => 60000,
        'provider_price' => 40000,
        'option1' => ''
    ]);
    
    // Image (Optional, for visual)
    
    echo "Created MKS Product: $goodsSeq\n";
} else {
    echo "MKS Product exists: {$existing->goods_seq}\n";
}
