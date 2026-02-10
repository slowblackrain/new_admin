<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// Check Goods 1000065 (Used in test)
$goods = DB::table('fm_goods')->where('goods_seq', 1000065)->first();
$option = DB::table('fm_goods_option')->where('goods_seq', 1000065)->first();

echo "Goods Seq: " . $goods->goods_seq . "\n";
echo "Goods Name: " . $goods->goods_name . "\n";
// echo "Goods Price (consumer_price): " . $goods->consumer_price . "\n"; // Column does not exist
// echo "Goods Price (price): " . $goods->price . "\n"; // Column does not exist

echo "Option Seq: " . $option->option_seq . "\n";
echo "Option Price (Sell Price): " . $option->price . "\n";
echo "Option Consumer Price: " . $option->consumer_price . "\n";
echo "Option Provider Price (Cost): " . $option->provider_price . "\n";
