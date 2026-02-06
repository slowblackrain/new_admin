<?php

use App\Models\Goods;
use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

// Cleanup existing
Goods::where('goods_scode', 'TEST_SET_PARENT')->delete();
Goods::where('goods_scode', 'TEST_SET_CHILD')->delete();

// Create Parent Goods (Normal Goods to be promoted to Set)
$parent = Goods::create([
    'goods_name' => 'Browser Test Set Parent',
    'goods_scode' => 'TEST_SET_PARENT',
    'goods_status' => 'normal',
    'goods_view' => 'look',
    'regist_date' => now(),
    'update_date' => now(),
]);

// Create Child Goods
$child = Goods::create([
    'goods_name' => 'Browser Test Set Child',
    'goods_scode' => 'TEST_SET_CHILD',
    'goods_status' => 'normal',
    'goods_view' => 'look',
    'regist_date' => now(),
    'update_date' => now(),
]);

echo "Created Goods: {$parent->goods_seq} (Parent), {$child->goods_seq} (Child)\n";
