<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking Indices...\n";

$tables = ['fm_scm_stock_move', 'fm_scm_stock_move_goods', 'fm_goods'];

foreach ($tables as $t) {
    echo "\n[$t Indices]\n";
    try {
        $indices = DB::select("SHOW INDEX FROM $t");
        foreach ($indices as $idx) {
            echo "Key_name: {$idx->Key_name}, Column_name: {$idx->Column_name}, Non_unique: {$idx->Non_unique}\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}
