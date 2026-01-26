<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Http\Controllers\Admin\Scm\ScmManageController;

// 1. Setup Data
$goods = DB::table('fm_goods')->orderBy('goods_seq', 'desc')->first();
$goods_id = $goods->goods_seq;

DB::table('fm_goods_supply')->updateOrInsert(['goods_seq' => $goods_id], ['stock' => 10, 'total_stock' => 10]);
$initial_stock = 10;
$new_stock = 15;

echo "Goods: $goods_id, Initial: $initial_stock, Target: $new_stock\n";

// 2. Mock Request
$request = new Request();
$request->merge([
    'stock' => [
        $goods_id => $new_stock
    ]
]);

// 3. Run Controller
$controller = new ScmManageController();
try {
    $controller->save_revision($request);
    echo "Controller Executed.\n";
} catch (\Exception $e) {
    echo "Controller Error: " . $e->getMessage() . "\n";
}

// 4. Verify
$final_stock = DB::table('fm_goods_supply')->where('goods_seq', $goods_id)->value('stock');
$revision_detail = DB::table('fm_scm_stock_revision_goods')
    ->where('goods_seq', $goods_id)
    ->orderBy('revision_seq', 'desc')
    ->first();

echo "Final Stock: $final_stock (Expected $new_stock)\n";
echo "Revision Log EA: " . ($revision_detail ? $revision_detail->ea : "None") . " (Expected 5)\n";

if ($final_stock == $new_stock && $revision_detail && $revision_detail->ea == 5) {
    echo "[PASS] Revision Logic Verified.\n";
} else {
    echo "[FAIL] Verification Failed.\n";
}
