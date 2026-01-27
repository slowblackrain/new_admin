<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

function showCols($table) {
    echo "Columns of $table:\n";
    try {
        $cols = DB::select("SHOW COLUMNS FROM $table");
        foreach ($cols as $c) {
            echo $c->Field . " (" . $c->Type . ")\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
    echo "\n";
}

showCols('fm_design_banner');
showCols('fm_design_banner_item');
