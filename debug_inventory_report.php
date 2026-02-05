<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Services\Scm\ScmInventoryService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== SCM Inventory Asset Report Verification ===\n";

$goodsSeq = rand(80000, 89999);
$whSeq = 1;
// Create Goods
DB::table('fm_goods')->insert([
    'goods_seq' => $goodsSeq,
    'goods_name' => 'AssetTestItem',
    'goods_code' => $goodsSeq, // Numeric
    'scm_category' => '001',
    'regist_date' => now(),
    'update_date' => now()
]);

// Ledger History
// Day 1: In 10 @ 1000
DB::table('fm_scm_ledger')->insert([
    'wh_seq' => $whSeq,
    'goods_seq' => $goodsSeq,
    'option_seq' => 0,
    'option_type' => 'option',
    'ldg_date' => '2026-01-01',
    'ldg_year' => 2026,
    'ldg_month' => '01',
    'regist_date' => '2026-01-01 10:00:00',
    'wh_cur_ea' => 10,
    'wh_cur_supply_price' => 1000,
    'cur_ea' => 10,
    'cur_supply_price' => 1000,
    'in_ea' => 10,
    'in_supply_price' => 1000
]);

// Day 2: In 10 @ 2000 => Avg Cost: ((10*1000) + (10*2000)) / 20 = 1500
DB::table('fm_scm_ledger')->insert([
    'wh_seq' => $whSeq,
    'goods_seq' => $goodsSeq,
    'option_seq' => 0,
    'option_type' => 'option',
    'ldg_date' => '2026-01-02',
    'ldg_year' => 2026,
    'ldg_month' => '01',
    'regist_date' => '2026-01-02 10:00:00',
    'wh_cur_ea' => 20,
    'wh_cur_supply_price' => 1500, // Weighted Avg
    'cur_ea' => 20,
    'cur_supply_price' => 1500,
    'in_ea' => 10,
    'in_supply_price' => 2000
]);

$service = new ScmInventoryService();

// Test 1: Query for Date 2026-01-01
$filters1 = ['date' => '2026-01-01', 'wh_seq' => $whSeq, 'keyword' => 'AssetTestItem'];
$result1 = $service->getInventoryList($filters1);
$item1 = $result1->first();

echo "Date 2026-01-01 -> ";
if ($item1 && $item1->wh_cur_ea == 10) {
    echo "PASS (Qty 10, Price {$item1->wh_cur_supply_price})\n";
} else {
    echo "FAIL (Expected 10, Got " . ($item1->wh_cur_ea ?? 'null') . ")\n";
}

// Test 2: Query for Date 2026-01-02
$filters2 = ['date' => '2026-01-02', 'wh_seq' => $whSeq, 'keyword' => 'AssetTestItem'];
$result2 = $service->getInventoryList($filters2);
$item2 = $result2->first();

echo "Date 2026-01-02 -> ";
if ($item2 && $item2->wh_cur_ea == 20 && $item2->wh_cur_supply_price == 1500) {
    echo "PASS (Qty 20, Price 1500)\n";
} else {
    echo "FAIL (Expected 20 @ 1500, Got " . ($item2->wh_cur_ea ?? 'null') . " @ " . ($item2->wh_cur_supply_price ?? 'null') . ")\n";
}

// Cleanup
DB::table('fm_goods')->where('goods_seq', $goodsSeq)->delete();
DB::table('fm_scm_ledger')->where('goods_seq', $goodsSeq)->delete();
