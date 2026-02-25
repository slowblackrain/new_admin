<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$gkd = DB::table('fm_goods')->where('goods_scode', 'like', 'GKD%')->first();
$gts = DB::table('fm_goods')->where('goods_scode', 'like', 'GTS%')->first();

if (!$gkd || !$gts) {
    echo "Could not find products to compare.\n";
    exit;
}

$gkd_array = (array) $gkd;
$gts_array = (array) $gts;

echo sprintf("%-35s | %-30s | %-30s\n", "Column", "GKD (" . $gkd->goods_scode . ")", "GTS (" . $gts->goods_scode . ")");
echo str_repeat("-", 100) . "\n";

foreach ($gkd_array as $key => $val) {
    if (strpos($key, 'shipping') !== false || strpos($key, 'seq') !== false || strpos($key, 'deliv') !== false || strpos($key, 'policy') !== false || strpos($key, 'group') !== false || $key == 'goods_kind') {
        echo sprintf("%-35s | %-30s | %-30s\n", $key, substr((string)$val, 0, 30), substr((string)$gts_array[$key], 0, 30));
    }
}
