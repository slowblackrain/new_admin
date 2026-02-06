<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$id = $app['encrypter']->getKey();

use App\Services\Scm\ScmOrderService;
use App\Services\Scm\ScmWarehousingService;
use Illuminate\Support\Facades\DB;

$orderService = app(ScmOrderService::class);
$whsService = app(ScmWarehousingService::class);

echo "Starting Warehousing Logic Verification...\n";

// Test Data
$traderSeq = DB::table('fm_scm_trader')->value('trader_seq') ?? 1;

// Fetch a goods that HAS an option
$validPair = DB::table('fm_goods')
    ->join('fm_goods_option', 'fm_goods.goods_seq', '=', 'fm_goods_option.goods_seq')
    ->select('fm_goods.goods_seq', 'fm_goods_option.option_seq')
    ->first();

if (!$validPair) {
    die("No valid goods/option found for testing.\n");
}

$goods = DB::table('fm_goods')->where('goods_seq', $validPair->goods_seq)->first();
$option = DB::table('fm_goods_option')->where('option_seq', $validPair->option_seq)->first();

echo "Target Trader: $traderSeq, Goods: {$goods->goods_seq}, Option: {$option->option_seq}\n";

// current stock
$startStock = DB::table('fm_goods_supply')->where('option_seq', $option->option_seq)->value('stock');

// 1. STANDARD WAREHOUSING TEST
echo "\n[TEST 1] Standard Warehousing (Order -> Receive)\n";
try {
    // 1-1. Create Order
    $sorderSeq = $orderService->saveOrder([
        'trader_seq' => $traderSeq,
        'sorder_type' => 'M',
        'item_goods_seq' => [$goods->goods_seq],
        'item_option_seq' => [$option->option_seq],
        'item_option_type' => ['option'],
        'item_ea' => [10],
        'item_supply_price' => [1000],
        'item_supply_tax' => [100],
        'item_goods_name' => [$goods->goods_name], // Just in case
        'item_option_name' => [$option->option_name ?? 'Option']
    ]);
    echo "  > Created Order Seq: $sorderSeq\n";

    // 1-2. Receive Goods (Partial: 5ea)
    $whsSeq1 = $whsService->saveWarehousing([
        'whs_type' => 'S',
        'status' => '1',
        'sorder_seq' => $sorderSeq,
        'trader_seq' => $traderSeq,
        'in_wh_seq' => 1,
        'goods_seq' => [$goods->goods_seq],
        'option_seq' => [$option->option_seq],
        'ea' => [5],
        'supply_price' => [1000],
        'supply_tax' => [100]
    ]);
    echo "  > Created Warehousing (Partial) Seq: $whsSeq1\n";

    // Check Order Status (Should still be 1: Ordered, partially received)
    $orderStatus = DB::table('fm_scm_order')->where('sorder_seq', $sorderSeq)->value('sorder_status');
    echo "  > Order Status (Expect 1): $orderStatus\n";

    // 1-3. Receive Remaining (5ea)
    $whsSeq2 = $whsService->saveWarehousing([
        'whs_type' => 'S',
        'status' => '1',
        'sorder_seq' => $sorderSeq,
        'trader_seq' => $traderSeq,
        'in_wh_seq' => 1,
        'goods_seq' => [$goods->goods_seq],
        'option_seq' => [$option->option_seq],
        'ea' => [5],
        'supply_price' => [1000],
        'supply_tax' => [100]
    ]);
    echo "  > Created Warehousing (Remaining) Seq: $whsSeq2\n";

    // Check Order Status (Should be 2: Complete)
    $orderStatus = DB::table('fm_scm_order')->where('sorder_seq', $sorderSeq)->value('sorder_status');
    echo "  > Order Status (Expect 2): $orderStatus\n";

    // Check Stock
    $endStock = DB::table('fm_goods_supply')->where('option_seq', $option->option_seq)->value('stock');
    echo "  > Stock Change: $startStock -> $endStock (Expect +10)\n";

} catch (Exception $e) {
    echo "  [ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}

// 2. EXCEPTION WAREHOUSING TEST
echo "\n[TEST 2] Exception Warehousing (Direct Receive)\n";
try {
    $whsSeqException = $whsService->saveWarehousing([
        'whs_type' => 'E',
        'status' => '1',
        'trader_seq' => $traderSeq,
        'in_wh_seq' => 1,
        'goods_seq' => [$goods->goods_seq],
        'option_seq' => [$option->option_seq],
        'ea' => [3],
        'supply_price' => [1000],
        'supply_tax' => [100]
    ]);
    echo "  > Created Exception Warehousing Seq: $whsSeqException\n";

    // Verify Auto-created Order
    $whsEntry = DB::table('fm_scm_warehousing')->where('whs_seq', $whsSeqException)->first();
    echo "  > Linked Order Seq: {$whsEntry->sorder_seq}\n";
    
    $autoOrder = DB::table('fm_scm_order')->where('sorder_seq', $whsEntry->sorder_seq)->first();
    echo "  > Linked Order Code: {$autoOrder->sorder_code} (Expect start with EC)\n";
    echo "  > Linked Order Type: {$autoOrder->sorder_type} (Expect T)\n";

    // Check Stock
    $finalStock = DB::table('fm_goods_supply')->where('option_seq', $option->option_seq)->value('stock');
    echo "  > Stock Change: $endStock -> $finalStock (Expect +3)\n";

} catch (Exception $e) {
    echo "  [ERROR] " . $e->getMessage() . "\n";
}

echo "\nVerification Complete.\n";
