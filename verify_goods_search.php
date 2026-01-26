<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "=================================================\n";
echo "       GOODS ADVANCED SEARCH VERIFICATION\n";
echo "=================================================\n\n";

// 1. Setup Test Data (Buying Service Item)
$goodsCode = 'FFF_TEST_ITEM';
DB::table('fm_goods')->updateOrInsert(
    ['goods_code' => $goodsCode],
    [
        'goods_name' => 'Buying Service Test Item',
        'goods_scode' => 'FFF-001', // Should trigger Pink Row logic
        'regist_date' => now(),
        'goods_status' => 'normal',
        'provider_status' => 1
    ]
);
$testGoods = DB::table('fm_goods')->where('goods_code', $goodsCode)->first();
// Ensure Logic 3 trigger: Order Item and Option
$orderSeq = 99999;
$itemSeq = 99999;

DB::table('fm_order_item')->insertOrIgnore([
    'item_seq' => $itemSeq,
    'order_seq' => $orderSeq,
    'goods_seq' => $testGoods->goods_seq
]);

DB::table('fm_order_item_option')->insertOrIgnore([
    'item_option_seq' => 99999,
    'item_seq' => $itemSeq,
    'order_seq' => $orderSeq,
    'step' => 50, // Trigger range
    'price' => 1000,
    'ea' => 1
]);


echo "[Step 1] Testing Search Logic (Controller Internal)...\n";

$controller = new \App\Http\Controllers\Admin\GoodsController($app->make(\App\Services\Goods\PriceCalculator::class));

// We just simulate request to catalog
$request = Request::create('/admin/goods/catalog', 'GET', [
    'keyword' => 'Buying Service',
    'goods_status' => ['normal']
]);

// Capture View Data? Hard in CLI without rendering.
// Better to check filtered query count via DB or trusted manual check.
// Let's verify route status at least.

try {
    $response = $kernel->handle($request);
    echo " -> Request Status: " . $response->getStatusCode() . "\n";
    
    // Check Content for "Thinking" or specific data?
    // In CLI response content is HTML.
    $content = $response->getContent();
    
    if (strpos($content, 'Buying Service Test Item') !== false) {
        echo " -> SUCCESS: Found filtered item in response HTML.\n";
    } else {
        echo " -> FAIL: Item not found in response.\n";
    }

    if (strpos($content, 'background-color: #ffccff') !== false) {
        echo " -> SUCCESS: Pink Row Logic (background-color: #ffccff) detected.\n";
    } else {
        echo " -> FAIL: Pink Row Logic not detected.\n";
    }

} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
