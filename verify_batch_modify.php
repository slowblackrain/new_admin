<?php

use App\Models\Goods;
use App\Services\Goods\BatchService;
use Illuminate\Http\Request;
use Tests\TestCase;

require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

echo "Starting BatchService Verification...\n";

// 1. Create Dummy Goods
$goods = Goods::create([
    'goods_name' => 'Batch Service Item',
    'goods_code' => 'BATCH_SVC_'.rand(100,999),
    'goods_status' => 'normal',
    'goods_view' => 'look',
    'regist_date' => now(),
    'update_date' => now()
]);

$id = $goods->goods_seq;

// 2. Instantiate Service
$service = new BatchService();

// 3. Simulate Request Data
$request = Request::create('/admin/goods/batch/modify', 'POST', [
    'goods_seq' => [$id],
    'batch_goods_status_yn' => 1,
    'batch_goods_status' => 'unsold',
    'batch_goods_view_yn' => 1,
    'batch_goods_view' => 'notLook'
]);

// 4. Run Service
$result = $service->batchModify($request);

// 5. Verify
$goods->refresh();

if ($goods->goods_status == 'unsold' && $goods->goods_view == 'notLook') {
    echo "[PASS] Status and View updated correctly.\n";
} else {
    echo "[FAIL] Status: " . $goods->goods_status . ", View: " . $goods->goods_view . "\n";
}

// Cleanup
$goods->delete();
