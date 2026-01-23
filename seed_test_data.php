<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

// 1. Create or Find Test Product
$goods = DB::table('fm_goods')->where('goods_code', 'BOX001')->first();
if ($goods) {
    $goodsId = $goods->goods_seq;
    echo "Found existing Goods ID: $goodsId\n";
} else {
    $goodsId = DB::table('fm_goods')->insertGetId([
        'goods_name' => 'Test Box Product',
        'goods_code' => 'BOX001',
        'goods_view' => 'look',
        'regist_date' => now(),
        'update_date' => now(),
    ]);
    echo "Created Goods ID: $goodsId\n";
}

// 2. Create or Find Option
$option = DB::table('fm_goods_option')->where('goods_seq', $goodsId)->first();
if ($option) {
    $optionId = $option->option_seq;
    echo "Found existing Option ID: $optionId\n";
} else {
    $optionId = DB::table('fm_goods_option')->insertGetId([
        'goods_seq' => $goodsId,
        'option1' => 'Red Color',
        'price' => 10000,
        'consumer_price' => 12000,
        // 'supply_price' => 8000, 
        // 'regist_date' => now(), // Column does not exist
    ]);
    echo "Created Option ID: $optionId\n";
}

// 3. Find an Order (first one)
$order = DB::table('fm_order')->first();
if (!$order) {
    echo "No Order Found! Creating one...\n";
    $orderSeq = DB::table('fm_order')->insertGetId([
        'order_id' => 'TESTORDER',
        'regist_date' => now(),
        'step' => 25, // Payment Confirmed
        'member_seq' => 1,
        'order_user_name' => 'Test User',
    ]);
    $order = DB::table('fm_order')->where('order_seq', $orderSeq)->first();
}
echo "Using Order: {$order->order_seq}\n";

// 4. Create Order Item (Parent) if not exists (check by order_seq and goods_seq)
$item = DB::table('fm_order_item')
    ->where('order_seq', $order->order_seq)
    ->where('goods_seq', $goodsId)
    ->first();

if ($item) {
    $itemId = $item->item_seq;
    echo "Found existing Order Item ID: $itemId\n";
} else {
    $itemId = DB::table('fm_order_item')->insertGetId([
        'order_seq' => $order->order_seq,
        'goods_seq' => $goodsId,
        'goods_name' => 'Test Box Product',
        'goods_code' => 'BOX001',
        'tax' => 'tax',
        'shipping_policy' => 'policy',
        // 'regist_date' => now(), // Column does not exist
    ]);
    echo "Created Order Item ID: $itemId for Order: {$order->order_seq}\n";
}

// 5. Create Order Item Option (Child) linked to Item
$itemOption = DB::table('fm_order_item_option')->where('item_seq', $itemId)->first();
if (!$itemOption) {
    DB::table('fm_order_item_option')->insert([
        'item_seq' => $itemId,
        'order_seq' => $order->order_seq,
        // 'goods_seq' => $goodsId, // Column does not exist
        // 'option_seq' => $optionId, // Column does not exist
        'option1' => 'Red Color',
        'ea' => 2,
        'price' => 10000,
        'step' => 25,
        // 'regist_date' => now(), // Column does not exist
    ]);
    echo "Created Order Item Option linked to Item $itemId\n";
} else {
    echo "Order Item Option already exists.\n";
}

// 6. Create Goods Image (for search result)
if (!DB::table('fm_goods_image')->where('goods_seq', $goodsId)->exists()) {
    DB::table('fm_goods_image')->insert([
        'goods_seq' => $goodsId,
        'image' => 'test_image.jpg',
        'cut_number' => 1,
        'regist_date' => now(),
    ]);
}

echo "Seed Complete.\n";
