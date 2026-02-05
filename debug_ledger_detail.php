<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Services\Scm\ScmLedgerDetailService;
use App\Services\Scm\ScmInventoryService;
use Illuminate\Support\Facades\DB;

echo "=== SCM Ledger Detail Verification ===\n";

$goodsSeq = rand(90000, 99999);
$whSeq = 1;

// 1. Create Goods (No options for simplicity in test)
DB::table('fm_goods')->insert([
    'goods_seq' => $goodsSeq, 'goods_name' => 'DetailTestItem', 'goods_code' => $goodsSeq,
    'scm_category' => '001', 'regist_date' => now(), 'update_date' => now()
]);
// Fm_goods_option required for join
DB::table('fm_goods_option')->insert([
    'goods_seq' => $goodsSeq, 'option_seq' => 1, 'option1' => 'Default', 'default_option' => 'y'
]);

// 2. Pre-Stock (Simulated by Ledger Entry BEFORE StartDate)
// StartDate = Today. Ledger Date = Yesterday.
$yesterday = date('Y-m-d', strtotime('-1 day'));
DB::table('fm_scm_ledger')->insert([
    'goods_seq' => $goodsSeq, 'wh_seq' => $whSeq, 'option_seq' => 1,
    'ldg_date' => $yesterday, 'ldg_year' => date('Y'), 'ldg_month' => date('m'),
    'cur_ea' => 10, 'cur_supply_price' => 1000, // Global
    'wh_cur_ea' => 10, 'wh_cur_supply_price' => 1000, // WH
    'regist_date' => $yesterday . ' 10:00:00'
]);

// 3. Transactions (Today)
$today = date('Y-m-d');
$now = date('H:i:s');

// A. Revision In (+5)
$revSeq = DB::table('fm_scm_stock_revision')->insertGetId([
    'revision_type' => 'in', 'wh_seq' => $whSeq, 'regist_date' => "$today 10:00:00",
    'revision_code' => 'REVtest', 'revision_status' => 1, 'admin_memo' => 'Restock', 
    //'total_ea' => 5 ... other fields optional
]);
DB::table('fm_scm_stock_revision_goods')->insert([
    'revision_seq' => $revSeq, 'goods_seq' => $goodsSeq, 'ea' => 5, 
    'supply_price' => 1000
]);

// B. Move Out (-3) (Assuming Move Logic creates entries in fm_scm_stock_move? 
// Service queries fm_scm_stock_move. But I implemented Query for stock_revision, stock_move...
// Service implementation query for Move:
// $q_move = DB::table('fm_scm_stock_move') ...
// Wait, in my Service implementation I commented out the specific Move logic and focused on Revision/In/Out/Order?
// Checking Service:
// // B. Stock Move ...
// // Service code had comments but did I implement it?
// // Ah, I see "B. Revision", "C. Warehousing", "D. CarryingOut", "E. Order".
// B. Stock Move
// From WH 1 to WH 2 (-3)
$moveSeq = DB::table('fm_scm_stock_move')->insertGetId([
    'out_wh_seq' => $whSeq, 'in_wh_seq' => 2, 'regist_date' => "$today 11:00:00",
    'move_code' => 'MOVetest', 'move_status' => 1
]);
DB::table('fm_scm_stock_move_goods')->insert([
    'move_seq' => $moveSeq, 'goods_seq' => $goodsSeq, 'ea' => 3
]);


// Checking Service Implementation...
$inventoryService = new ScmInventoryService();
$detailService = new ScmLedgerDetailService($inventoryService);

$filters = ['start_date' => $today, 'end_date' => $today, 'wh_seq' => $whSeq];
$result = $detailService->getHistory($goodsSeq, $filters);

if (!$result) {
    echo "FAIL: No result returned.\n";
    exit;
}

echo "Pre-Stock: " . $result['pre_stock'] . " (Expected 10)\n";

echo "Transactions:\n";
foreach ($result['history'] as $item) {
    echo "[{$item->date}] {$item->type} In:{$item->in_qty} Out:{$item->out_qty} Balance:{$item->current_stock}\n";
}

// Cleanup
// DB::table('fm_goods')->where('goods_seq', $goodsSeq)->delete();
// DB::table('fm_goods_option')->where('goods_seq', $goodsSeq)->delete();
// DB::table('fm_scm_ledger')->where('goods_seq', $goodsSeq)->delete();
// DB::table('fm_scm_stock_revision')->where('goods_seq', $goodsSeq)->delete(); // Incorrect query logic (need join or loop?)
// DB::table('fm_scm_stock_move_goods')->where('goods_seq', $goodsSeq)->delete();
// Manually cleanup later if needed.
echo "Goods Seq: $goodsSeq Created for verification.\n";
