<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$catCount = DB::table('fm_category')->count();
$goodsCount = DB::table('fm_goods')->count();
$optionCount = DB::table('fm_goods_option')->count();

echo "Categories: " . $catCount . "\n";
echo "Goods: " . $goodsCount . "\n";
echo "Goods Options: " . $optionCount . "\n";
