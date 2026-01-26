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

echo "--- Stock Logic Verification (Robust) ---\n";

// 1. Find a valid Goods Supply -> Option -> Goods
$supply = DB::table('fm_goods_supply')->first(); // Just get first available
if (!$supply) { 
    echo "No supply found. Creating one...\n";
    // Create dummy chain
    $uniqueCode = rand(100000,999999);
    $goodsSeq = DB::table('fm_goods')->insertGetId([
         'goods_name' => 'Test Goods ' . $uniqueCode,
         'goods_code' => $uniqueCode
    ]);
    
    $optionSeq = DB::table('fm_goods_option')->insertGetId([
        'goods_seq' => $goodsSeq,
        'option1' => 'Opt1',
        'consumer_price' => 1000,
        'option_seq' => 99999 // Force ID if possible or let auto-inc
    ]);
    
    // Check if auto-inc worked or if we need to fetch
    if ($optionSeq == 0) $optionSeq = DB::getPdo()->lastInsertId();

    DB::table('fm_goods_supply')->insert([
        'option_seq' => $optionSeq,
        'stock' => 100,
        'reservation15' => 0,
        'reservation25' => 0,
        'goods_seq' => $goodsSeq
    ]);
    
    $supply = DB::table('fm_goods_supply')->where('option_seq', $optionSeq)->first();
}

$goodsOption = DB::table('fm_goods_option')->where('option_seq', $supply->option_seq)->first();
$goodsSeq = $goodsOption->goods_seq;

echo "Target Option Seq: {$supply->option_seq}, Goods Seq: {$goodsSeq}\n";
echo "Initial Stock: {$supply->stock}, Res15: {$supply->reservation15}, Res25: {$supply->reservation25}\n";

// 2. Create Dummy Order Data
$orderSeq = date('YmdHis') . rand(100,999);
DB::table('fm_order')->insert([
    'order_seq' => $orderSeq,
    'step' => 15, // Started at 15
    'regist_date' => now()
]);

$itemSeq = DB::table('fm_order_item')->insertGetId([
    'order_seq' => $orderSeq,
    'goods_seq' => $goodsSeq
]);

DB::table('fm_order_item_option')->insert([
    'item_seq' => $itemSeq,
    'order_seq' => $orderSeq,
    'ea' => 1,
    'option1' => $goodsOption->option1,
    'option2' => $goodsOption->option2,
    'option3' => $goodsOption->option3,
    'option4' => $goodsOption->option4,
    'option5' => $goodsOption->option5,
    'step' => 15
]);

echo "Created Order {$orderSeq}\n";

// 3. Mock Test
$controller = new OrderProcessController();

// A. 15 -> 25
echo "\n[Test A] Deposit Confirm (15 -> 25)...\n";
$req = new Request(['order_seq' => $orderSeq, 'mode' => 'deposit_confirm']);
$controller->updateStatus($req);

$sAfterA = DB::table('fm_goods_supply')->where('option_seq', $supply->option_seq)->first();
echo "State: Stock={$sAfterA->stock} (Exp:{$supply->stock}), Res15={$sAfterA->reservation15} (Exp:".($supply->reservation15 - 1)."), Res25={$sAfterA->reservation25} (Exp:".($supply->reservation25 + 1).")\n";

// Reset Step to 45 for next test (skip intermediate logic in this rapid test)
DB::table('fm_order')->where('order_seq', $orderSeq)->update(['step' => 45]);

// B. 45 -> 55
echo "\n[Test B] Export (45 -> 55)...\n";
$req = new Request(['order_seq' => $orderSeq, 'mode' => 'export_goods']);
$controller->updateStatus($req);

$sAfterB = DB::table('fm_goods_supply')->where('option_seq', $supply->option_seq)->first();
echo "State: Stock={$sAfterB->stock} (Exp:".($supply->stock - 1)."), Res25={$sAfterB->reservation25} (Exp:".($supply->reservation25).") (Wait, Res25 should be back to initial if we incremented then decremented)\n";

// C. Cancel (55 -> 95)
echo "\n[Test C] Cancel (55 -> 95)...\n";
$req = new Request(['order_seq' => $orderSeq, 'mode' => 'cancel_order']);
$controller->updateStatus($req);

$sAfterC = DB::table('fm_goods_supply')->where('option_seq', $supply->option_seq)->first();
echo "State: Stock={$sAfterC->stock} (Exp:{$supply->stock})\n";

// Cleanup
DB::table('fm_order')->where('order_seq', $orderSeq)->delete();
DB::table('fm_order_item')->where('order_seq', $orderSeq)->delete();
DB::table('fm_order_item_option')->where('order_seq', $orderSeq)->delete();
