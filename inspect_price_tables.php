<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== TABLES MATCHING fm_member% ===\n";
$tables = DB::select("SHOW TABLES LIKE 'fm_member%'");
foreach ($tables as $t) {
    $vals = array_values((array)$t);
    echo $vals[0] . "\n";
}

echo "\n=== TABLES MATCHING fm_goods_group% ===\n";
$tables = DB::select("SHOW TABLES LIKE 'fm_goods_group%'");
foreach ($tables as $t) {
    $vals = array_values((array)$t);
    echo $vals[0] . "\n";
}

echo "\n=== DESCRIBE fm_goods_option ===\n";
$columns = DB::select("DESCRIBE fm_goods_option");
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n=== DESCRIBE fm_category_group (If exists check) ===\n";
// Maybe category has group prices?
$tables = DB::select("SHOW TABLES LIKE 'fm_category_group%'");
foreach ($tables as $t) {
    $vals = array_values((array)$t);
    echo $vals[0] . "\n";
}
