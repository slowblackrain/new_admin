<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function checkTable($table) {
    echo "Checking $table columns:\n";
    try {
        $cols = DB::select("DESCRIBE $table");
        foreach ($cols as $col) {
            echo " - " . $col->Field . "\n";
        }
    } catch (\Exception $e) {
        echo "Error checking $table: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

checkTable('fm_offer');
checkTable('fm_goods_image');
