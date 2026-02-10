<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 1. Insert Goods
$exists = DB::table('fm_goods')->where('goods_seq', 206718)->exists();
if (!$exists) {
    try {
        DB::table('fm_goods')->insert([
            'goods_seq' => 206718,
            'goods_name' => 'Dummy Printing Product',
            'goods_code' => '206718', // Use goods_seq as goods_code
            'goods_status' => 'normal',
            'goods_view' => 'look',
            'regist_date' => date('Y-m-d H:i:s'),
            'update_date' => date('Y-m-d H:i:s'),
            'provider_seq' => 1,
            'summary' => 'Test',
            'goods_type' => 'goods',
            'package_yn' => 'n',
            'package_yn_suboption' => 'n',
            'provider_status' => '1', // Active provider status
        ]);
        echo "Created dummy goods 206718.\n";
    } catch (\Exception $e) {
        echo "Error creating goods: " . $e->getMessage() . "\n";
    }
} else {
    echo "Goods 206718 already exists.\n";
}

// 2. Insert Default Option (Price)
$optExists = DB::table('fm_goods_option')->where('goods_seq', 206718)->where('default_option', 'y')->exists();
if (!$optExists) {
    try {
        DB::table('fm_goods_option')->insert([
            'goods_seq' => 206718,
            'default_option' => 'y',
            'consumer_price' => 1000,
            'price' => 1000, 
            'provider_price' => 800, 
            'option_title' => 'Default',
            'option1' => '',
            'option_code' => '',
        ]);
        echo "Created default option for 206718.\n";
    } catch (\Exception $e) {
         echo "Error creating option: " . $e->getMessage() . "\n";
    }
} else {
    echo "Default option for 206718 already exists.\n";
}
