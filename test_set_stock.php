<?php
use Illuminate\Support\Facades\DB;

$setGoods = DB::table('fm_goods_set')->where('main_seq', '>', 0)->first();
if (!$setGoods) {
    echo "No set products found in DB.\n";
    exit;
}

$parent_seq = $setGoods->main_seq;
$components = DB::table('fm_goods_set')->where('main_seq', $parent_seq)->get();

echo "Set Product Seq: $parent_seq\n";
foreach($components as $c) {
    $stock = DB::table('fm_goods')->where('goods_seq', $c->goods_seq)->value('tot_stock');
    echo "Component: " . $c->goods_seq . " | Req Qty per set: " . $c->goods_ea . " | Current Stock: " . $stock . "\n";
}

// Generate an order item option for mock
$itemSeq = DB::table('fm_order_item_option')->max('item_seq') + 1;
DB::table('fm_order_item_option')->insert([
    'item_seq' => $itemSeq,
    'order_seq' => 'TEST_ORDER_01',
    'ea' => 2 // order 2 sets
]);

echo "Simulating deduction for 2 sets... (Expected deduction = 2 * req_qty)\n";
$service = App()->make(App\Services\Admin\Goods\GoodsSetService::class);
$service->deductStockForSet('TEST_ORDER_01', $parent_seq, $itemSeq);

echo "After Deduction:\n";
foreach($components as $c) {
    $stock = DB::table('fm_goods')->where('goods_seq', $c->goods_seq)->value('tot_stock');
    echo "Component: " . $c->goods_seq . " | Current Stock: " . $stock . "\n";
}

// Cleanup mock
DB::table('fm_order_item_option')->where('item_seq', $itemSeq)->delete();
