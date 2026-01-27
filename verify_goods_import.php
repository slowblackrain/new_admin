<?php

use App\Imports\GoodsImport;
use App\Models\Goods;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "Starting GoodsImport Verification...\n";

// 1. Test Registry Mode w/ Mock Data (Simulating Excel Row)
$row = [
    'goods_name' => 'Test Import Goods ' . rand(100,999),
    'goods_code' => 'TEST_IMP_'.rand(1000,9999),
    'status' => 'normal',
    'price' => 5000,
    'stock' => 100
];

$import = new GoodsImport('regist');
// Directly call collection method to bypass Excel file parsing for logic test
$import->collection(new Collection([$row]));

$goods = Goods::where('goods_code', $row['goods_code'])->first();

if ($goods) {
    echo "[PASS] Goods Created: " . $goods->goods_name . "\n";
    
    // Check Option
    $option = \App\Models\GoodsOption::where('goods_seq', $goods->goods_seq)->first();
    if ($option && $option->price == 5000) {
        echo "[PASS] Option Price 5000 Verified.\n";
    } else {
        echo "[FAIL] Option Price Mismatch.\n";
    }

    // Check Stock
    $supply = DB::table('fm_goods_supply')->where('goods_seq', $goods->goods_seq)->first();
    if ($supply && $supply->stock == 100) {
        echo "[PASS] Stock 100 Verified.\n";
    } else {
        echo "[FAIL] Stock Mismatch.\n";
    }
    
    // Cleanup
    $goods->delete();
    // Cascade delete usually handles options/supply, but let's be safe if no cascade
    DB::table('fm_goods_option')->where('goods_seq', $goods->goods_seq)->delete();
    DB::table('fm_goods_supply')->where('goods_seq', $goods->goods_seq)->delete();
    
} else {
    echo "[FAIL] Goods not found.\n";
}
