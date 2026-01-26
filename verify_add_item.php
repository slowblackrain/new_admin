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

echo "--- Add Item Logic Verification ---\n";

// 1. Create Goods & Option (Initial Stock 100, Res15 0)
$code = rand(100000, 999999);
$goodsSeq = DB::table('fm_goods')->insertGetId(['goods_name' => 'AddItemTest', 'goods_code' => $code]);
$optSeq = DB::table('fm_goods_option')->insertGetId([
    'goods_seq' => $goodsSeq, 
    'option1' => 'OptTest', 
    'consumer_price' => 1000,
    'price' => 1000,
    'option_seq' => rand(50000, 59999)
]);
DB::table('fm_goods_supply')->insert([
    'goods_seq' => $goodsSeq, 'option_seq' => $optSeq, 'stock' => 100, 'reservation15' => 0
]);

// 2. Create Order (Step 15)
$orderSeq = date('YmdHis').rand(10,99);
DB::table('fm_order')->insert(['order_seq' => $orderSeq, 'step' => 15, 'regist_date' => now()]);

echo "Created Order {$orderSeq} (Step 15) and Goods {$goodsSeq} (Opt {$optSeq}).\n";

// 3. Add Item (Expect Res15 + 2)
$controller = new OrderProcessController();
$req = new Request([
    'order_seq' => $orderSeq,
    'goods_seq' => $goodsSeq,
    'option_seq' => $optSeq,
    'ea' => 2
]);

echo "Adding 2 items...\n";
$resp = $controller->addItem($req);
echo "Response: " . json_encode($resp->getData()) . "\n";

// 4. Verify
$orderItem = DB::table('fm_order_item')->where('order_seq', $orderSeq)->first();
$orderItemOpt = DB::table('fm_order_item_option')->where('order_seq', $orderSeq)->first();
$supply = DB::table('fm_goods_supply')->where('option_seq', $optSeq)->first();

if ($orderItem && $orderItemOpt) {
    echo "Item Created: OK (ItemSeq: {$orderItem->item_seq}, EA: {$orderItemOpt->ea})\n";
} else {
    echo "Item Created: FAIL\n";
}

echo "Res15: {$supply->reservation15} (Exp: 2)\n";

// Cleanup
DB::table('fm_order')->where('order_seq', $orderSeq)->delete();
DB::table('fm_order_item')->where('order_seq', $orderSeq)->delete();
DB::table('fm_order_item_option')->where('order_seq', $orderSeq)->delete();
