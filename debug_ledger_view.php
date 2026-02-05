<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Services\Scm\ScmLedgerService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== SCM Ledger Logic Verification ===\n";

// 1. Setup Data
$whSeq = 1;
// Use random seq to minimize conflict
$goodsSeq = rand(80000, 89999);
$today = date('Y-m-d');
$uniqueCode = 'LTEST' . $goodsSeq . date('His');

// Clean up potential collision
DB::table('fm_scm_ledger')->where('goods_seq', $goodsSeq)->delete();
DB::table('fm_goods')->where('goods_seq', $goodsSeq)->delete();
DB::table('fm_goods')->where('goods_code', $uniqueCode)->delete();

echo "Creating goods {$goodsSeq} / {$uniqueCode}\n";

// Create Test Goods
try {
    DB::table('fm_goods')->insert([
        'goods_seq' => $goodsSeq,
        'goods_name' => 'LedgerTestItem',
        'goods_code' => $uniqueCode,
        'scm_category' => '001',
        'regist_date' => now(),
        'update_date' => now()
    ]);
} catch (\Exception $e) {
    echo "Insert failed unique: " . $e->getMessage() . "\n";
    // Try to continue if it was just code collision?
    exit;
}

// Insert Mock Ledger Data
// Scenario:
// Pre: 10 ea @ 1000 KRW = 10,000
// In: 10 ea @ 2000 KRW = 20,000 (Expensive batch incoming)
// Out: 5 ea. 
// Expect Out Unit Price = (10000 + 20000) / (10 + 10) = 30000 / 20 = 1500 KRW.

DB::table('fm_scm_ledger')->insert([
    'ldg_date' => $today,
    'ldg_year' => date('Y'),
    'ldg_month' => date('m'),
    'wh_seq' => $whSeq,
    'goods_seq' => $goodsSeq,
    'option_type' => 'option',
    'option_seq' => 0,
    
    // Data Fields
    'pre_ea' => 10,
    'pre_supply_price' => 1000, 
    
    'in_ea' => 10,
    'in_supply_price' => 20000, // Total Value of Inbound
    
    'out_ea' => 5,
    'out_supply_price' => 0, // Should be ignored/overwritten by calc for display? No, service calculation is for DISPLAY only. DB might store 0 or snapshot.
    
    'cur_ea' => 15, // (10 + 10 - 5)
    'cur_supply_price' => 0,
    'regist_date' => now()
]);

echo "inserted mock ledger data.\n";

// 2. Test Service Logic
$service = new ScmLedgerService();
$filters = [
    'start_date' => $today,
    'end_date' => $today,
    'keyword' => 'LedgerTestItem'
];

$result = $service->getLedgerList($filters);
$item = $result->items()[0];

echo "Fetched Ledger Item:\n";
echo "Pre Price (Calc): " . $item->calc_pre_price . " (Expect 10000)\n";
echo "In Price (Calc): " . $item->calc_in_price . " (Expect 20000)\n";
echo "Out Unit Price (Calc): " . $item->calc_out_unit_price . " (Expect 1500)\n";
echo "Out Total Price (Calc): " . $item->calc_out_price . " (Expect 7500)\n";

if ($item->calc_pre_price == 10000 && $item->calc_out_unit_price == 1500 && $item->calc_out_price == 7500) {
    echo "SUCCESS: Logic verified.\n";
} else {
    echo "FAILURE: Logic mismatch.\n";
}
