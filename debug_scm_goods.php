<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Services\Scm\ScmGoodsService;
use Illuminate\Support\Facades\DB;

echo "=== SCM Goods Management Verification ===\n";

$goodsSeq1 = rand(90000, 99999);
$goodsSeq2 = rand(90000, 99999);
$whSeq = 1;

// Helper to create goods
function createGoods($seq, $name, $stock, $safeStock, $whStock, $whSeq) {
    DB::table('fm_goods')->insert([
        'goods_seq' => $seq, 'goods_name' => $name, 'goods_code' => $seq, 
        'scm_category' => '001', 'regist_date' => now(), 'update_date' => now()
    ]);
    
    DB::table('fm_goods_supply')->insert([
        'goods_seq' => $seq, 'stock' => $stock, 'safe_stock' => $safeStock, 
        'supply_price' => 1000
    ]);
    
    DB::table('fm_scm_location_link')->insert([
        'wh_seq' => $whSeq, 'goods_seq' => $seq, 'ea' => $whStock,
        'option_seq' => 0, 'option_type' => 'option'
    ]);
}

// Case 1: Warning (Stock < Safe)
// Global: 5 < 10
// WH: 5 < 10
createGoods($goodsSeq1, 'WarningItem', 5, 10, 5, $whSeq);

// Case 2: Safe (Stock >= Safe)
// Global: 10 >= 5
// WH: 10 >= 5
createGoods($goodsSeq2, 'SafeItem', 10, 5, 10, $whSeq);

$service = new ScmGoodsService();

echo "\n--- Test 1: Global Warning Check ---\n";
// Case 1 should be in warning list
$filtersWarning = ['warning_only' => true, 'keyword' => 'WarningItem'];
$result1 = $service->getScmGoodsList($filtersWarning);
$item1 = $result1->first();

if ($item1 && $item1->goods_seq == $goodsSeq1) {
    echo "PASS: WarningItem found in Warning List.\n";
} else {
    echo "FAIL: WarningItem NOT found in Warning List.\n";
}

// Case 2 should NOT be in warning list
$filtersSafe = ['warning_only' => true, 'keyword' => 'SafeItem'];
$result2 = $service->getScmGoodsList($filtersSafe);
$item2 = $result2->first();

if (!$item2) {
    echo "PASS: SafeItem NOT found in Warning List.\n";
} else {
    echo "FAIL: SafeItem FOUND in Warning List (Unexpected).\n";
}

echo "\n--- Test 2: WH Specific Check ---\n";
// Should verify WH stock is retrieved
$filtersWH = ['wh_seq' => $whSeq, 'keyword' => 'WarningItem'];
$result3 = $service->getScmGoodsList($filtersWH);
$item3 = $result3->first();

if ($item3 && $item3->wh_stock == 5) {
    echo "PASS: WH Stock retrieved correctly (5).\n";
} else {
    echo "FAIL: WH Stock incorrect. Got " . ($item3->wh_stock ?? 'null') . "\n";
}


// Cleanup
DB::table('fm_goods')->whereIn('goods_seq', [$goodsSeq1, $goodsSeq2])->delete();
DB::table('fm_goods_supply')->whereIn('goods_seq', [$goodsSeq1, $goodsSeq2])->delete();
DB::table('fm_scm_location_link')->whereIn('goods_seq', [$goodsSeq1, $goodsSeq2])->delete();
