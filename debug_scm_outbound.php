<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Services\Scm\ScmCarryingOutService;
use App\Services\Scm\ScmLedgerService;

echo "Starting SCM Outbound (Carrying Out) Test...\n";

// 1. Setup Data (Assume Goods Seq 56838 exists)
$goodsSeq = 56838;
$optionSeq = 122944;
$whSeq = 1;
$traderSeq = 1;

// 1.5 Ensure Stock Exists
$supplyExists = DB::table('fm_goods_supply')
    ->where('goods_seq', $goodsSeq)
    ->where('option_seq', $optionSeq)
    ->exists();

if (!$supplyExists) {
    echo "Initializing Stock...\n";
    DB::table('fm_goods_supply')->insert([
        'goods_seq' => $goodsSeq,
        'option_seq' => $optionSeq,
        'stock' => 100,
        'total_stock' => 100,
        'badstock' => 0,
        'reservation15' => 0,
        'reservation25' => 0,
        'safe_stock' => 10,
    ]);
}

// Check current stock
$beforeStock = DB::table('fm_goods_supply')
    ->where('goods_seq', $goodsSeq)
    ->where('option_seq', $optionSeq)
    ->value('stock');

echo "Current Stock: " . $beforeStock . "\n";

// 2. Execute Carrying Out
$service = new ScmCarryingOutService(new ScmLedgerService());

$items = [
    [
        'goods_seq' => $goodsSeq,
        'option_seq' => $optionSeq,
        'option_type' => 'option',
        'ea' => 5, // Release 5 items
        'supply_price' => 10000
    ]
];

try {
    $croSeq = $service->processCarryingOut($whSeq, $traderSeq, $items);
    echo "Carrying Out Processed. CRO Seq: " . $croSeq . "\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit;
}

// 3. Verify Stock Update
$afterStock = DB::table('fm_goods_supply')
    ->where('goods_seq', $goodsSeq)
    ->where('option_seq', $optionSeq)
    ->value('stock');

echo "After Stock: " . $afterStock . "\n";

if ($beforeStock - 5 == $afterStock) {
    echo "SUCCESS: Stock decremented correctly.\n";
} else {
    echo "FAIL: Stock mismatch. Expected " . ($beforeStock - 5) . ", Got " . $afterStock . "\n";
}

// 4. Verify Ledger
$ledger = DB::table('fm_scm_ledger')
    ->where('goods_seq', $goodsSeq)
    ->where('option_seq', $optionSeq)
    ->where('wh_seq', $whSeq)
    ->where('ldg_date', date('Y-m-d'))
    ->first();

if ($ledger) {
    echo "Ledger Entry Found:\n";
    echo "IN: " . $ledger->in_ea . ", OUT: " . $ledger->out_ea . "\n";
    echo "Stock (wh_cur_ea): " . $ledger->wh_cur_ea . "\n";
    
    if ($ledger->out_ea >= 5) {
         echo "SUCCESS: Ledger records OUT movement.\n";
    } else {
         echo "FAIL: Ledger OUT count incorrect.\n";
    }
} else {
    echo "FAIL: No ledger entry found for today.\n";
}
