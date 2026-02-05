<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Services\Scm\ScmStockMoveService;
use App\Services\Scm\ScmLedgerService;
use Illuminate\Support\Facades\DB;

echo "=== SCM Stock Move Verification ===\n";

$whA = 1;
$whB = 2; // Assuming 2 exists, otherwise script might fail or insert new
$goodsSeq = rand(90000, 99999);
$moveQty = 5;
$optionSeq = 0;

echo "Target Goods: {$goodsSeq}\n";

// 1. Setup Initial State
// Clear related data
DB::table('fm_goods')->where('goods_seq', $goodsSeq)->delete();
DB::table('fm_scm_location_link')->where('goods_seq', $goodsSeq)->delete();
DB::table('fm_scm_stock_move_goods')->where('goods_seq', $goodsSeq)->delete();
DB::table('fm_scm_ledger')->where('goods_seq', $goodsSeq)->delete();

// Create Goods with unique numeric code
$uniqueCode = $goodsSeq; // Use seq as code for test

DB::table('fm_goods')->insert([
    'goods_seq' => $goodsSeq,
    'goods_name' => 'MoveTestItem',
    'goods_code' => $uniqueCode,
    'scm_category' => '001',
    'regist_date' => now(),
    'update_date' => now()
]);

// Seed Source WH Stock (A) = 10
DB::table('fm_scm_location_link')->insert([
    'wh_seq' => $whA,
    'goods_seq' => $goodsSeq,
    'option_seq' => $optionSeq,
    'option_type' => 'option',
    'ea' => 10,
    'location_code' => 'LOC-A'
]);

// Seed Target WH Stock (B) = 0 (or empty)
DB::table('fm_scm_location_link')->insert([
    'wh_seq' => $whB,
    'goods_seq' => $goodsSeq,
    'option_seq' => $optionSeq,
    'option_type' => 'option',
    'ea' => 0,
    'location_code' => 'LOC-B'
]);

echo "Initial Stock: WH-A: 10, WH-B: 0\n";

// 2. Execute Move A -> B (Qty: 5)
$ledgerService = new ScmLedgerService();
$moveService = new ScmStockMoveService($ledgerService);

$items = [[
    'goods_seq' => $goodsSeq,
    'option_seq' => $optionSeq,
    'option_type' => 'option',
    'ea' => $moveQty
]];

try {
    $moveSeq = $moveService->processStockMove($whA, $whB, $items, "Debug Move Test");
    echo "Move Created: SEQ {$moveSeq}\n";
} catch (\Exception $e) {
    echo "Move Failed: " . $e->getMessage() . "\n";
    exit;
}

// 3. Verify Stock
$stockA = DB::table('fm_scm_location_link')->where('wh_seq', $whA)->where('goods_seq', $goodsSeq)->value('ea');
$stockB = DB::table('fm_scm_location_link')->where('wh_seq', $whB)->where('goods_seq', $goodsSeq)->value('ea');

echo "Final Stock: WH-A: {$stockA} (Expect 5), WH-B: {$stockB} (Expect 5)\n";

if ($stockA == 5 && $stockB == 5) {
    echo "STOCK UPDATE: PASS\n";
} else {
    echo "STOCK UPDATE: FAIL\n";
}

// 4. Verify Ledger
// WH-A should have OUT 5
$ledgerA = DB::table('fm_scm_ledger')
    ->where('wh_seq', $whA)
    ->where('goods_seq', $goodsSeq)
    ->where('ldg_date', date('Y-m-d'))
    ->first();

// WH-B should have IN 5
$ledgerB = DB::table('fm_scm_ledger')
    ->where('wh_seq', $whB)
    ->where('goods_seq', $goodsSeq)
    ->where('ldg_date', date('Y-m-d'))
    ->first();

if ($ledgerA && $ledgerA->out_ea == 5) {
    echo "LEDGER A (OUT): PASS\n";
} else {
    echo "LEDGER A (OUT): FAIL (Got " . ($ledgerA->out_ea ?? 'null') . ")\n";
}

if ($ledgerB && $ledgerB->in_ea == 5) {
    echo "LEDGER B (IN): PASS\n";
} else {
    echo "LEDGER B (IN): FAIL (Got " . ($ledgerB->in_ea ?? 'null') . ")\n";
}
