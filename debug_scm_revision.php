<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Services\Scm\ScmRevisionService;
use App\Services\Scm\ScmLedgerService;

echo "Starting SCM Stock Revision Test...\n";

// 1. Setup Data
$goodsSeq = 56838;
$optionSeq = 122944;
$whSeq = 1;

// Ensure initial stock is known (Reset to 100 for consistency)
DB::table('fm_goods_supply')
    ->where('goods_seq', $goodsSeq)
    ->where('option_seq', $optionSeq)
    ->update(['stock' => 100, 'total_stock' => 100]);

echo "Reset Stock to 100.\n";

$service = new ScmRevisionService(new ScmLedgerService());

// 2. Test Case A: Positive Revision (+5)
$itemsA = [[
    'goods_seq' => $goodsSeq,
    'option_seq' => $optionSeq,
    'option_type' => 'option',
    'ea' => 5,
    'reason' => 'Found lost items'
]];

echo "Executing Revision (+5)...\n";
$revSeqA = $service->processRevision($whSeq, $itemsA, "Test Revision +5");
echo "Revision A SEQ: $revSeqA\n";

// Check Stock (Should be 105)
$stockA = DB::table('fm_goods_supply')->where('goods_seq', $goodsSeq)->where('option_seq', $optionSeq)->value('stock');
echo "Stock after +5: $stockA " . ($stockA == 105 ? "[PASS]" : "[FAIL]") . "\n";

// Check Ledger (IN should increase)
$ledgerA = DB::table('fm_scm_ledger')
    ->where('goods_seq', $goodsSeq)
    ->where('option_seq', $optionSeq)
    ->where('ldg_date', date('Y-m-d'))
    ->first();
echo "Ledger A - IN: {$ledgerA->in_ea}, OUT: {$ledgerA->out_ea}\n";


// 3. Test Case B: Negative Revision (-3)
$itemsB = [[
    'goods_seq' => $goodsSeq,
    'option_seq' => $optionSeq,
    'option_type' => 'option',
    'ea' => -3,
    'reason' => 'Damaged items'
]];

echo "Executing Revision (-3)...\n";
$revSeqB = $service->processRevision($whSeq, $itemsB, "Test Revision -3");
echo "Revision B SEQ: $revSeqB\n";

// Check Stock (Should be 102)
$stockB = DB::table('fm_goods_supply')->where('goods_seq', $goodsSeq)->where('option_seq', $optionSeq)->value('stock');
echo "Stock after -3: $stockB " . ($stockB == 102 ? "[PASS]" : "[FAIL]") . "\n";

// Check Ledger (OUT should increase)
$ledgerB = DB::table('fm_scm_ledger')
    ->where('goods_seq', $goodsSeq)
    ->where('option_seq', $optionSeq)
    ->where('ldg_date', date('Y-m-d'))
    ->first();
echo "Ledger B - IN: {$ledgerB->in_ea}, OUT: {$ledgerB->out_ea}\n";

// Summary
if ($stockA == 105 && $stockB == 102 && $ledgerB->in_ea >= 5 && $ledgerB->out_ea >= 3) {
    echo "ALL TESTS PASSED.\n";
} else {
    echo "TEST FAILED.\n";
}
