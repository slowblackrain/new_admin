<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use App\Services\Scm\ScmOrderService;
use App\Services\Scm\ScmLedgerService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

$service = app(ScmOrderService::class);

echo "--- SCM Ledger Test ---\n";

// 1. Setup - Partial Warehousing
$goodsSeq = 1001;
// Reset Stock & Ledger
DB::table('fm_goods_supply')->updateOrInsert(
    ['goods_seq' => $goodsSeq, 'option_seq' => 500],
    ['stock' => 100, 'total_stock' => 100]
);
DB::table('fm_scm_ledger')->where('goods_seq', $goodsSeq)->where('option_seq', 500)->delete();
$startStock = DB::table('fm_goods_supply')->where('goods_seq', $goodsSeq)->value('stock');

// Create & Confirm Order
$goodsInfo = ['goods_seq' => $goodsSeq, 'goods_name' => 'AutoTestItem', 'goods_code' => 'AT001'];
$orderOption = ['order_seq' => 9999, 'order_ea' => 10]; // Order 10 ea
$goodsOption = [
    'option_seq' => 500,
    'option_type' => 'option',
    'consumer_price' => 10000,
    'price' => 9000,
    'stock' => 100,
    'badstock' => 0,
    'safe_stock' => 20,
];

$draftId = $service->createAutoOrderDraft($goodsInfo, $orderOption, $goodsOption, true); 
$orderSeqs = $service->confirmAutoOrders([$draftId]);
$orderSeq = $orderSeqs[0];

echo "Order Created: $orderSeq (10 EA)\n";

// 2. Perform Warehousing (5 EA) -> Should Trigger Ledger Update
echo "--- Partial Warehousing (5 EA) ---\n";
$whsSeq = $service->processWarehousing($orderSeq, [
    ['goods_seq' => $goodsSeq, 'option_seq' => 500, 'ea' => 5]
]);

// 3. Verify Ledger
$today = Carbon::now()->format('Y-m-d');
$ledger = DB::table('fm_scm_ledger')
    ->where('goods_seq', $goodsSeq)
    ->where('option_seq', 500)
    ->where('ldg_date', $today)
    ->first();

if ($ledger) {
    echo "Ledger Found!\n";
    echo "Date: $ledger->ldg_date\n";
    echo "IN EA: $ledger->in_ea (Expected: 5)\n";
    echo "PRE EA: $ledger->pre_ea (Expected: 100)\n";
    echo "CUR EA: $ledger->cur_ea (Expected: 105)\n";
} else {
    echo "Ledger Not Found!\n";
}

// 4. Perform Another Warehousing (5 EA) -> Ledger Should Accumulate
echo "--- Remaining Warehousing (5 EA) ---\n";
$service->processWarehousing($orderSeq, [
    ['goods_seq' => $goodsSeq, 'option_seq' => 500, 'ea' => 5]
]);

$ledger2 = DB::table('fm_scm_ledger')
    ->where('goods_seq', $goodsSeq)
    ->where('option_seq', 500)
    ->where('ldg_date', $today)
    ->first();

if ($ledger2) {
    echo "Updated Ledger Found!\n";
    echo "IN EA: $ledger2->in_ea (Expected: 10)\n";
    echo "CUR EA: $ledger2->cur_ea (Expected: 110)\n";
} else {
    echo "Ledger Update Failed!\n";
}
