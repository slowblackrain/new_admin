<?php

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\Order\OrderProcessController;
use Illuminate\Http\Request;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- Package Exchange Logic Verification ---\n";

// 1. Setup Package (Comp A + Comp B)
$compACode = rand(100000, 999999);
$goodsA = DB::table('fm_goods')->insertGetId(['goods_name' => 'CompA', 'goods_code' => $compACode]);
$optA = DB::table('fm_goods_option')->insertGetId(['goods_seq' => $goodsA, 'option1' => 'A', 'option_seq' => rand(10000,19999)]);
DB::table('fm_goods_supply')->insert(['goods_seq' => $goodsA, 'option_seq' => $optA, 'stock' => 100, 'reservation25' => 0]);

$compBCode = rand(100000, 999999);
$goodsB = DB::table('fm_goods')->insertGetId(['goods_name' => 'CompB', 'goods_code' => $compBCode]);
$optB = DB::table('fm_goods_option')->insertGetId(['goods_seq' => $goodsB, 'option1' => 'B', 'option_seq' => rand(20000,29999)]);
DB::table('fm_goods_supply')->insert(['goods_seq' => $goodsB, 'option_seq' => $optB, 'stock' => 100, 'reservation25' => 0]);

$pkgCode = rand(100000, 999999);
$goodsPkg = DB::table('fm_goods')->insertGetId(['goods_name' => 'Package', 'goods_code' => $pkgCode, 'package_yn' => 'y']);
$optPkg = DB::table('fm_goods_option')->insertGetId([
    'goods_seq' => $goodsPkg,
    'option1' => 'Pkg',
    'package_option_seq1' => $optA,
    'package_unit_ea1' => 2,
    'package_option_seq2' => $optB,
    'package_unit_ea2' => 1,
    'option_seq' => rand(30000,39999)
]);

// 2. Setup Single Item (Standard)
$stdCode = rand(100000, 999999);
$goodsStd = DB::table('fm_goods')->insertGetId(['goods_name' => 'Standard', 'goods_code' => $stdCode]);
$optStd = DB::table('fm_goods_option')->insertGetId(['goods_seq' => $goodsStd, 'option1' => 'Std', 'option_seq' => rand(40000,49999)]);
DB::table('fm_goods_supply')->insert(['goods_seq' => $goodsStd, 'option_seq' => $optStd, 'stock' => 50, 'reservation25' => 10]);

echo "Created Package {$optPkg} (A x2, B x1) and Standard {$optStd}\n";

// 3. Create initial order with Standard Item (Step 25)
$orderSeq = date('YmdHis').rand(10,99);
DB::table('fm_order')->insert(['order_seq' => $orderSeq, 'step' => 25, 'regist_date' => now()]);

$itemSeq = DB::table('fm_order_item')->insertGetId(['order_seq' => $orderSeq, 'goods_seq' => $goodsStd]);
$itemOptSeq = DB::table('fm_order_item_option')->insertGetId([
    'item_seq' => $itemSeq,
    'order_seq' => $orderSeq,
    'ea' => 1,
    'option1' => 'Std',
    'step' => 25
]);

$controller = new OrderProcessController();

// 4. Perform Exchange: Standard -> Package
echo "\n[Test] Exchange Standard -> Package (Step 25)...\n";
// Replacing Standard (Old) with Package (New)
// Expected: Standard Res25 -1 (Restore), Package Components Res25 +2/+1 (Deduct)
// Note: Logic in ModifyStock: 
// - New Item (Deduct): Step 25 -> Res25 Increment
// - Old Item (Restore): Step 25 -> Res25 Decrement

$req = new Request([
    'order_seq' => $orderSeq,
    'original_item_seq' => $itemOptSeq,
    'new_goods_seq' => $goodsPkg,
    'new_option_seq' => $optPkg,
    'change_code' => 'test_exchange'
]);

$controller->replaceItem($req);

// 5. Verify
$stockA = DB::table('fm_goods_supply')->where('option_seq', $optA)->first();
$stockB = DB::table('fm_goods_supply')->where('option_seq', $optB)->first();
$stockStd = DB::table('fm_goods_supply')->where('option_seq', $optStd)->first();

echo "CompA Res25: {$stockA->reservation25} (Exp: 2)\n";
echo "CompB Res25: {$stockB->reservation25} (Exp: 1)\n";
echo "Std   Res25: {$stockStd->reservation25} (Exp: 9) [Initial 10 - 1]\n";

// Cleanup
DB::table('fm_order')->where('order_seq', $orderSeq)->delete();
DB::table('fm_order_item')->where('order_seq', $orderSeq)->delete();
DB::table('fm_order_item_option')->where('order_seq', $orderSeq)->delete();
