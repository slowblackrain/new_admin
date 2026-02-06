<?php

use App\Models\Goods;
use App\Models\GoodsSet;
use App\Services\Goods\GoodsSetService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

// Mock Admin Auth
Auth::shouldReceive('guard')->with('admin')->andReturnSelf();
Auth::shouldReceive('user')->andReturn((object)['mname' => 'TestAdmin']);

$service = new GoodsSetService();

echo "1. Creating Test Goods...\n";
// Create Parent Goods (Set)
$parent = Goods::create([
    'goods_name' => 'Test Set Parent',
    'goods_scode' => 'SET001',
    'goods_status' => 'normal',
    'goods_view' => 'look',
    'regist_date' => now(),
    'update_date' => now(),
]);

// Create Child Goods
$child = Goods::create([
    'goods_name' => 'Test Set Child',
    'goods_scode' => 'CHILD001',
    'goods_status' => 'normal',
    'goods_view' => 'look',
    'regist_date' => now(),
    'update_date' => now(),
]);

echo "Created Parent: {$parent->goods_seq}, Child: {$child->goods_seq}\n";

echo "2. Adding Main Set (Parent)...\n";
// Add Parent as Main Set (main_seq = 0)
$res1 = $service->add(0, $parent->goods_seq);
echo "Add Parent Result: " . json_encode($res1) . "\n";

echo "3. Adding Child to Set...\n";
// Add Child to Parent (main_seq = parent->goods_seq)
$res2 = $service->add($parent->goods_seq, $child->goods_seq, 5);
echo "Add Child Result: " . json_encode($res2) . "\n";

echo "4. Verifying DB...\n";
$mainSet = GoodsSet::where('main_seq', 0)->where('goods_seq', $parent->goods_seq)->first();
$childSet = GoodsSet::where('main_seq', $parent->goods_seq)->where('goods_seq', $child->goods_seq)->first();

if ($mainSet && $childSet && $childSet->goods_ea == 5) {
    echo "PASS: Set structure created correctly.\n";
} else {
    echo "FAIL: DB check failed.\n";
    print_r($mainSet);
    print_r($childSet);
}

// Cleanup
GoodsSet::where('main_seq', 0)->where('goods_seq', $parent->goods_seq)->delete();
GoodsSet::where('main_seq', $parent->goods_seq)->where('goods_seq', $child->goods_seq)->delete();
$parent->delete();
$child->delete();
