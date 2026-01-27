<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Find a supply record with stock > 0
$supply = DB::table('fm_goods_supply')->where('stock', '>', 0)->first();

if ($supply) {
    echo "Found Valid Supply:\n";
    print_r($supply);
    
    $goods = DB::table('fm_goods')->where('goods_seq', $supply->goods_seq)->first();
    echo "\nCorresponding Goods: {$goods->goods_seq} - {$goods->goods_name}\n";
    
    // Check Option if linked
    if ($supply->option_seq) {
        echo "Linked Option Seq: {$supply->option_seq}\n";
    }
} else {
    echo "NO STOCK DATA FOUND IN ENTIRE DB.\n";
}
