<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$id = $app['encrypter']->getKey();

use App\Services\Scm\ScmCarryingOutService;
use Illuminate\Support\Facades\DB;

$croService = app(ScmCarryingOutService::class);

echo "Starting Carrying Out Logic Verification...\n";

// Test Data
$traderSeq = DB::table('fm_scm_trader')->value('trader_seq') ?? 1;

// Fetch valid goods
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

// 1. Check Initial Stock
$initialStock = DB::table('fm_goods_supply')->where('option_seq', $option->option_seq)->value('stock');
echo "Initial Stock: $initialStock\n";

// 2. Create Carrying Out (Deduct 5)
echo "\n[TEST 1] Create Carrying Out (Deduct 5ea)\n";
try {
    $croSeq = $croService->saveCarryingOut([
        'trader_seq' => $traderSeq,
        'cro_type' => 'E',
        'status' => '1', // Complete
        'wh_seq' => 1,
        'goods_seq' => [$goods->goods_seq],
        'option_seq' => [$option->option_seq],
        'option_type' => ['option'],
        'ea' => [5],
        'supply_price' => [1000],
        'supply_tax' => [100]
    ]);
    echo "  > Created Carrying Out Seq: $croSeq\n";

    $afterDeductStock = DB::table('fm_goods_supply')->where('option_seq', $option->option_seq)->value('stock');
    echo "  > Stock Change: $initialStock -> $afterDeductStock (Expect -5)\n";

    if ($afterDeductStock == $initialStock - 5) {
        echo "  > [PASS] Deduction Success\n";
    } else {
        echo "  > [FAIL] Deduction Mismatch\n";
    }

} catch (Exception $e) {
    echo "  [ERROR] " . $e->getMessage() . "\n";
    exit;
}

// 3. Delete Carrying Out (Revert 5)
echo "\n[TEST 2] Delete Carrying Out (Revert)\n";
try {
    $croService->deleteCarryingOut($croSeq);
    echo "  > Deleted Carrying Out Seq: $croSeq\n";

    $finalStock = DB::table('fm_goods_supply')->where('option_seq', $option->option_seq)->value('stock');
    echo "  > Stock Change: $afterDeductStock -> $finalStock (Expect +5)\n";

    if ($finalStock == $initialStock) {
        echo "  > [PASS] Reversion Success\n";
    } else {
        echo "  > [FAIL] Reversion Mismatch\n";
    }

} catch (Exception $e) {
    echo "  [ERROR] " . $e->getMessage() . "\n";
}

echo "\nVerification Complete.\n";
