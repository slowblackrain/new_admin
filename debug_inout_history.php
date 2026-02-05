<?php

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use App\Services\Scm\ScmInOutHistoryService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

echo "=== SCM In/Out History Verification ===\n";

$goodsSeq = rand(90000, 99999);
$whSeq = 1;
// Create Goods
DB::table('fm_goods')->insert([
    'goods_seq' => $goodsSeq,
    'goods_name' => 'InOutTestItem',
    'goods_code' => $goodsSeq, 
    'scm_category' => '001',
    'regist_date' => now(),
    'update_date' => now()
]);

// Scenario:
// Day 0 (Before Period): In 10 @ 1000. (Pre-Stock source)
// Day 1 (Start of Period): In 5 @ 1000.
// Day 2 (Middle of Period): Out 3.
// Day 3 (Before End of Period): In 2 @ 2000.
// End of Period.

$day0 = Carbon::now()->subDays(5)->format('Y-m-d');
$day1 = Carbon::now()->subDays(3)->format('Y-m-d');
$day2 = Carbon::now()->subDays(2)->format('Y-m-d');
$day3 = Carbon::now()->subDays(1)->format('Y-m-d');

// Day 0: Pre-Stock
DB::table('fm_scm_ledger')->insert([
    'wh_seq' => $whSeq, 'goods_seq' => $goodsSeq, 'option_seq' => 0, 'option_type' => 'option',
    'ldg_date' => $day0, 'regist_date' => $day0.' 10:00:00',
    'wh_cur_ea' => 10, 'wh_cur_supply_price' => 1000,
    'in_ea' => 10, 'in_supply_price' => 1000 // Initial In
]);

// Day 1: In 5
// WAC: (10*1000 + 5*1000)/15 = 1000.
DB::table('fm_scm_ledger')->insert([
    'wh_seq' => $whSeq, 'goods_seq' => $goodsSeq, 'option_seq' => 0, 'option_type' => 'option',
    'ldg_date' => $day1, 'regist_date' => $day1.' 10:00:00',
    'wh_cur_ea' => 15, 'wh_cur_supply_price' => 1000,
    'in_ea' => 5, 'in_supply_price' => 1000
]);

// Day 2: Out 3
// WAC: 1000.
DB::table('fm_scm_ledger')->insert([
    'wh_seq' => $whSeq, 'goods_seq' => $goodsSeq, 'option_seq' => 0, 'option_type' => 'option',
    'ldg_date' => $day2, 'regist_date' => $day2.' 10:00:00',
    'wh_cur_ea' => 12, 'wh_cur_supply_price' => 1000,
    'out_ea' => 3, 'out_supply_price' => 1000,
    'in_ea' => 0 // Explicit 0 for clarity
]);

// Day 3: In 2 @ 2000
// WAC: (12*1000 + 2*2000) / 14 = (12000+4000)/14 = 16000/14 = 1142.85
DB::table('fm_scm_ledger')->insert([
    'wh_seq' => $whSeq, 'goods_seq' => $goodsSeq, 'option_seq' => 0, 'option_type' => 'option',
    'ldg_date' => $day3, 'regist_date' => $day3.' 10:00:00',
    'wh_cur_ea' => 14, 'wh_cur_supply_price' => 1142.85,
    'in_ea' => 2, 'in_supply_price' => 2000
]);

$service = new ScmInOutHistoryService();

// Query Range: Day 1 to Day 3 (Exclude Day 0)
// Expected:
// Pre: 10 (from Day 0)
// In: 5 + 2 = 7
// Out: 3
// Cur: 10 + 7 - 3 = 14

$filters = [
    'start_date' => $day1,
    'end_date' => $day3,
    'wh_seq' => $whSeq,
    'keyword' => 'InOutTestItem'
];

$result = $service->getPeriodSummary($filters);
$item = $result->first();

if (!$item) {
    echo "FAIL: Item not found\n";
} else {
    echo "Item Found: {$item->goods_name}\n";
    echo "Pre Ea: Expect 10 -> Got {$item->pre_ea} | " . ($item->pre_ea == 10 ? "PASS" : "FAIL") . "\n";
    echo "In Ea : Expect 7  -> Got {$item->in_ea}  | " . ($item->in_ea == 7 ? "PASS" : "FAIL") . "\n";
    echo "Out Ea: Expect 3  -> Got {$item->out_ea}  | " . ($item->out_ea == 3 ? "PASS" : "FAIL") . "\n";
    echo "Cur Ea: Expect 14 -> Got {$item->cur_ea}  | " . ($item->cur_ea == 14 ? "PASS" : "FAIL") . "\n";
    echo "Cur Price: Expect approx 1142.85 -> Got {$item->cur_price}\n";
}

// Cleanup
DB::table('fm_goods')->where('goods_seq', $goodsSeq)->delete();
DB::table('fm_scm_ledger')->where('goods_seq', $goodsSeq)->delete();
