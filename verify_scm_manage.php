<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Http\Controllers\Admin\Scm\ScmManageController;
use Illuminate\Http\Request;
use App\Models\Scm\ScmLocationLink;
use App\Models\Scm\ScmStockMove;
use Illuminate\Support\Facades\DB;

echo "Verifying Stock Move Logic...\n";

// 1. Setup Test Data
$goods_code = 'TEST001';
$goods = DB::table('fm_goods')->where('goods_code', $goods_code)->first();

if (!$goods) {
    echo "Creating Test Goods...\n";
    // Check if goods_seq 1 is free?
    // We don't force goods_seq 1 to avoid conflicts. Auto-increment.
    $goods_seq = DB::table('fm_goods')->insertGetId([
        'goods_name' => 'Test Goods',
        'goods_code' => $goods_code,
        'regist_date' => now()
    ]);
} else {
    echo "Using Existing Test Goods: {$goods->goods_seq}\n";
    $goods_seq = $goods->goods_seq;
}

$out_wh = 1; // Default
$in_wh = 2; // Test Warehouse

// Ensure Warehouses exist
DB::table('fm_scm_warehouse')->updateOrInsert(['wh_seq' => $out_wh], ['wh_name' => 'Main WH']);
DB::table('fm_scm_warehouse')->updateOrInsert(['wh_seq' => $in_wh], ['wh_name' => 'Sub WH']);

// Reset Location Stock for Test
ScmLocationLink::where('goods_seq', $goods_seq)->delete();
ScmLocationLink::create([
    'wh_seq' => $out_wh,
    'goods_seq' => $goods_seq,
    'goods_name' => 'Test',
    'goods_code' => $goods_code,
    'option_type' => 'option',
    'option_seq' => 0,
    'ea' => 100 // Initial Stock
]);

echo "[SETUP] Out WH Stock: 100, In WH Stock: 0\n";

// 2. Execute Move (50 ea)
$controller = new ScmManageController();
$request = Request::create('/admin/scm_manage/stockmove/save', 'POST', [
    'out_wh_seq' => $out_wh,
    'in_wh_seq' => $in_wh,
    'stock' => [$goods_seq => 50],
    'admin_memo' => 'Test Move CLI'
]);

try {
    $controller->stockmove_save($request);
} catch (\Illuminate\Validation\ValidationException $e) {
    echo "[ERROR] Validation Check Failed: " . implode(', ', $e->errors()) . "\n";
} catch (\Exception $e) {
    // Check if it's a redirect (success)
    if (strpos(get_class($e), 'Redirect') === false) {
         echo "[INFO] Controller Exception: " . $e->getMessage() . "\n";
    }
}

// 3. Verify Result
$out_stock = ScmLocationLink::where('wh_seq', $out_wh)->where('goods_seq', $goods_seq)->value('ea');
$in_stock = ScmLocationLink::where('wh_seq', $in_wh)->where('goods_seq', $goods_seq)->value('ea');
$move_rec = ScmStockMove::latest('move_seq')->first();

echo "[RESULT] Out WH Stock: $out_stock (Expected 50)\n";
echo "[RESULT] In WH Stock: $in_stock (Expected 50)\n";

if ($out_stock == 50 && $in_stock == 50 && $move_rec && $move_rec->total_ea == 50) {
    echo "SUCCESS: Stock Moved Correctly.\n";
} else {
    echo "FAIL: Stock Mismatch.\n";
}
