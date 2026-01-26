<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\Scm\ScmBasicController;
use App\Http\Controllers\Admin\Scm\ScmManageController;
use App\Models\Scm\ScmLocationLink;
use App\Models\Scm\ScmStockMove;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

echo "=================================================\n";
echo "       SCM INTEGRATION TEST SCENARIO\n";
echo "=================================================\n\n";

// 1. Setup Environment (Warehouses and Goods)
echo "[Step 1] Setting up Environment...\n";

// Create Warehouses
$whA_seq = 1001; 
$whB_seq = 1002;

DB::table('fm_scm_warehouse')->updateOrInsert(['wh_seq' => $whA_seq], ['wh_name' => 'Integration WH A']);
DB::table('fm_scm_warehouse')->updateOrInsert(['wh_seq' => $whB_seq], ['wh_name' => 'Integration WH B']);

// Create Product
$goods_code = 'INTEGRATION_TEST_001';
$goods = DB::table('fm_goods')->where('goods_code', $goods_code)->first();
if (!$goods) {
    $goods_seq = DB::table('fm_goods')->insertGetId([
        'goods_name' => 'Integration Test Item',
        'goods_code' => $goods_code,
        'regist_date' => now()
    ]);
} else {
    $goods_seq = $goods->goods_seq;
}

// Reset Stock
DB::table('fm_goods_supply')->updateOrInsert(['goods_seq' => $goods_seq], ['stock' => 0, 'total_stock' => 0]);
ScmLocationLink::where('goods_seq', $goods_seq)->delete();

echo " -> Warehouses created: #$whA_seq, #$whB_seq\n";
echo " -> Product created/found: #$goods_seq ($goods_code)\n";
echo " -> Stock Reset.\n\n";


// 2. Initial Stock Revision (Inbound to WH A)
echo "[Step 2] Initial Stock Revision (Inbound 100 ea to WH A)...\n";

$initial_qty = 100;
// Simulate request to save_revision
// Note: save_revision currently defaults to WH 1. 
// Ideally we should make it accept a param, but for parity with legacy it might default to main.
// For this test, let's manually insert the 'Revision' to simulate what the controller does, 
// OR temporarily modify the controller? 
// Actually, earlier updateLocationStock(1, ...) hardcoded to 1.
// Let's rely on the fact that existing logic targets WH 1.
// BUT for this test we want WH A ($whA_seq).
// So we will manually create the ScmLocationLink since the UI doesn't support multi-warehouse revision yet (Parity gap identified).

ScmLocationLink::create([
    'wh_seq' => $whA_seq,
    'goods_seq' => $goods_seq,
    'goods_name' => 'Integration Test Item',
    'goods_code' => $goods_code,
    'option_type' => 'option',
    'option_seq' => 0,
    'ea' => $initial_qty
]);
DB::table('fm_goods_supply')->where('goods_seq', $goods_seq)->update(['stock' => $initial_qty, 'total_stock' => $initial_qty]);

echo " -> Manually forced stock into WH A ($initial_qty).\n";
$stockA = ScmLocationLink::where('wh_seq', $whA_seq)->where('goods_seq', $goods_seq)->value('ea');
echo " -> Verified WH A Stock: $stockA\n\n";


// 3. Stock Movement (WH A -> WH B)
echo "[Step 3] Moving Stock (50 ea) from WH A to WH B...\n";

$move_qty = 50;
$controller = new ScmManageController();
$request = Request::create('/admin/scm_manage/stockmove/save', 'POST', [
    'out_wh_seq' => $whA_seq,
    'in_wh_seq' => $whB_seq,
    'stock' => [$goods_seq => $move_qty],
    'admin_memo' => 'Integration Test Move'
]);

try {
    $controller->stockmove_save($request);
} catch (\Exception $e) {
    if (strpos(get_class($e), 'Redirect') === false) {
         echo " -> Exception: " . $e->getMessage() . "\n";
    }
}

$stockA = ScmLocationLink::where('wh_seq', $whA_seq)->where('goods_seq', $goods_seq)->value('ea');
$stockB = ScmLocationLink::where('wh_seq', $whB_seq)->where('goods_seq', $goods_seq)->value('ea');
$move_rec = ScmStockMove::latest('move_seq')->first();

echo " -> Move executed.\n";
echo " -> WH A Stock: $stockA (Expected " . ($initial_qty - $move_qty) . ")\n";
echo " -> WH B Stock: $stockB (Expected $move_qty)\n";
echo " -> Move Record Total: " . ($move_rec ? $move_rec->total_ea : 'N/A') . "\n\n";

if ($stockA == 50 && $stockB == 50) {
    echo "SUCCESS: Stock Movement Verification Passed.\n";
} else {
    echo "FAIL: Stock Mismatch.\n";
}

echo "\n=================================================\n";
echo "       TEST COMPLETE\n";
echo "=================================================\n";
