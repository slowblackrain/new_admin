<?php

use App\Services\Goods\GoodsSetService;
use App\Models\GoodsSet;
use App\Models\Goods;

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$service = new GoodsSetService();

// 1. Find a set that has children
$parent = GoodsSet::where('main_seq', 0)->whereHas('goods')->first();

if (!$parent) {
    echo "No parent sets found.\n";
    exit;
}

echo "Testing with Parent Set ID: " . $parent->goods_seq . "\n";

// 2. Call the service
$children = $service->getSetChildren($parent->goods_seq);

echo "Found " . $children->count() . " children.\n";

foreach ($children as $child) {
    echo "Child Set Seq: " . $child->set_seq . "\n";
    echo "Child Goods Seq: " . $child->goods_seq . "\n";
    if ($child->goods) {
        echo " - Goods Name: " . $child->goods->goods_name . "\n";
        echo " - Scode: " . $child->goods->goods_scode . "\n";
    } else {
        echo " - [ERROR] Goods relation is NULL (Should be filtered out if whereHas works)\n";
    }
}

echo "Verification Complete.\n";
