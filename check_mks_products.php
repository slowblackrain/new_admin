<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$products = Illuminate\Support\Facades\DB::table('fm_goods')
    ->where('goods_scode', 'like', '%mks%')
    ->orWhere('goods_code', 'like', '%mks%')
    ->select('goods_seq', 'goods_name', 'goods_code', 'goods_scode')
    ->limit(10)
    ->get();

echo "Products matching 'mks':\n";
foreach ($products as $p) {
    echo "Seq: {$p->goods_seq}, Name: {$p->goods_name}, Code: {$p->goods_code}, Scode: {$p->goods_scode}\n";
}

$legacy_blocks = ['FFF', 'MTS', 'MXT', 'OOO', 'QQQ'];
foreach ($legacy_blocks as $block) {
    $count = Illuminate\Support\Facades\DB::table('fm_goods')->where('goods_scode', 'like', $block . '%')->count();
    echo "Block $block count: $count\n";
}
