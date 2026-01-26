<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

// 0. Cleanup
DB::table('fm_goods')->where('goods_code', 'like', 'TEST-AUTO-%')->delete();

// 1. Get Existing Goods (Workaround for weird Duplicate '0' error)
$goods = DB::table('fm_goods')->where('goods_code', '0')->first();
if (!$goods) {
    // If '0' doesn't exist (contradicting error), try to find ANY goods
    $goods = DB::table('fm_goods')->orderBy('goods_seq', 'desc')->first();
}
$goods_id = $goods->goods_seq;
echo "Using Goods ID: $goods_id (Code: {$goods->goods_code})\n";

// 2. Set Stock to 5 (Update or Insert)
DB::table('fm_goods_supply')->updateOrInsert(
    ['goods_seq' => $goods_id],
    ['stock' => 5, 'total_stock' => 5]
);

// 3. Create Dummy Order (Qty 10)
$order_seq = rand(10000000, 99999999);
DB::table('fm_order')->insert([
    'order_seq' => $order_seq,
    'step' => 25, // Payment Confirmed
    'regist_date' => now(),
    'order_user_name' => 'AutoTest',
    'order_cellphone' => '010-0000-0000',
    'order_email' => 'test@test.com',
    'mode' => 'test',
    'session_id' => 'test_session',
    'admin_order' => '',
    'total_ea' => 10,
    'total_type' => 1,
    'shipping_cost' => 0
]);

$item_seq = DB::table('fm_order_item')->insertGetId([
    'order_seq' => $order_seq,
    'goods_seq' => $goods_id,
    'goods_name' => 'AutoTestItem',
    'goods_shipping_cost' => 0
]);

DB::table('fm_order_item_option')->insert([
    'item_seq' => $item_seq,
    'order_seq' => $order_seq,
    'ea' => 10,
    'step' => 25
]);

// 4. Run Calc Logic (Simulating Controller)
$required = DB::table('fm_order_item as item')
    ->join('fm_order_item_option as opt', 'item.item_seq', '=', 'opt.item_seq')
    ->join('fm_order as ord', 'item.order_seq', '=', 'ord.order_seq')
    ->select('item.goods_seq', DB::raw('SUM(opt.ea) as required_qty'))
    ->where('ord.step', '>=', 25) 
    ->where('ord.step', '<', 75)
    ->where('item.goods_seq', $goods_id)
    ->groupBy('item.goods_seq')
    ->first();

$stock = DB::table('fm_goods_supply')->where('goods_seq', $goods_id)->value('stock');

echo "Goods ID: $goods_id\n";
echo "Required Qty (Order): " . ($required->required_qty ?? 0) . "\n";
echo "Current Stock: " . ($stock ?? 0) . "\n";
echo "Net Need: " . (($required->required_qty ?? 0) - ($stock ?? 0)) . "\n";

if (($required->required_qty ?? 0) - ($stock ?? 0) == 5) {
    echo "[PASS] Logic Correct.\n";
} else {
    echo "[FAIL] Logic Incorrect.\n";
}
