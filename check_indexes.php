<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

function printIndexes($table) {
    echo "--- Indexes for table: {$table} ---\n";
    $indexes = DB::select("SHOW INDEXES FROM {$table}");
    foreach ($indexes as $idx) {
        echo "Key: {$idx->Key_name}, Col: {$idx->Column_name}, Non_unique: {$idx->Non_unique}\n";
    }
    echo "\n";
}

printIndexes('fm_goods');
printIndexes('fm_goods_option');
printIndexes('fm_goods_supply');
