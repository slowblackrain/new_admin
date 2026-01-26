<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Admin\GoodsBatchController;
use Illuminate\Http\Request;

echo "\n[Batch Update Verification] Starting...\n";

// Helper
function createMockRequest($data) {
    global $app;
    $req = Request::create('/test', 'POST', $data);
    $app->instance('request', $req);
    return $req;
}

try {
    DB::beginTransaction();
    $controller = new GoodsBatchController();

    // 1. Get a target goods
    $target = DB::table('fm_goods')->orderBy('goods_seq', 'desc')->first();
    if(!$target) throw new Exception("No goods found to test.");
    
    echo "Target: " . $target->goods_seq . " (" . $target->goods_name . ")\n";
    print_r($target);
    // 2. Prepare Update Data
    $opt = DB::table('fm_goods_option')->where('goods_seq', $target->goods_seq)->where('default_option', 'y')->first();
    if(!$opt) $opt = DB::table('fm_goods_option')->where('goods_seq', $target->goods_seq)->first();
    
    $currentPrice = $opt ? $opt->price : 0;
    $newPrice = $currentPrice + 100;
    $newStatus = ($target->goods_status == 'normal') ? 'runout' : 'normal';
    
    echo "Original Price (Option): $currentPrice\n";
    
    $data = [
        'goods_seq' => [$target->goods_seq],
        'updates' => [
            $target->goods_seq => [
                'price' => $newPrice,
                'goods_status' => $newStatus,
                'goods_view' => 'look'
            ]
        ]
    ];
    
    $req = createMockRequest($data);
    
    // 3. Exec Update
    echo "Updating...\n";
    $controller->update($req);
    
    // 4. Verify
    $updated = DB::table('fm_goods')->where('goods_seq', $target->goods_seq)->first();
    $updatedOpt = DB::table('fm_goods_option')->where('goods_seq', $target->goods_seq)->first();
    
    echo "Updated Price (Option): " . $updatedOpt->price . " (Expected: $newPrice)\n";
    echo "Updated Status: " . $updated->goods_status . " (Expected: $newStatus)\n";
    
    if($updatedOpt->price != $newPrice) throw new Exception("Price update failed");
    if($updated->goods_status != $newStatus) throw new Exception("Status update failed");

    // Rollback
    DB::rollBack();
    echo "\n[SUCCESS] Batch Update Verified.\n";

} catch (Exception $e) {
    DB::rollBack();
    echo "\n[ERROR] " . $e->getMessage() . "\n";
    echo $e->getTraceAsString();
}
