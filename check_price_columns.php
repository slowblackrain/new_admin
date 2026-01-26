<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['fm_goods', 'fm_goods_option'];

foreach ($tables as $t) {
    echo "\n--- $t ---\n";
    try {
        $columns = DB::select("DESCRIBE $t");
        foreach ($columns as $col) {
            // Filter likely price-related columns
            if (preg_match('/price|cost|won|rate|sale|consumer|supply|option_price/i', $col->Field)) {
                echo $col->Field . " (" . $col->Type . ")\n";
            }
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
