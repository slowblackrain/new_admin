<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Checking Schema for Link and Move tables...\n";

// Check if fm_scm_location_link exists
$exists = DB::select("SHOW TABLES LIKE 'fm_scm_location_link'");
if (count($exists) > 0) {
    echo "\n[fm_scm_location_link]\n";
    $cols = DB::select("SHOW COLUMNS FROM fm_scm_location_link");
    foreach ($cols as $c) {
        echo "{$c->Field} ({$c->Type})\n";
    }
} else {
    echo "\n[fm_scm_location_link] NOT FOUND. Checking latest date table...\n";
    // Try to find the latest date one
    $tables = DB::select("SHOW TABLES LIKE 'fm_scm_location_link_20%'");
    $latest = end($tables);
    $val = array_values((array)$latest)[0];
    echo "Checking $val...\n";
    $cols = DB::select("SHOW COLUMNS FROM $val");
    foreach ($cols as $c) {
        echo "{$c->Field} ({$c->Type})\n";
    }
}

echo "\n[fm_scm_stock_move]\n";
$cols = DB::select("SHOW COLUMNS FROM fm_scm_stock_move");
foreach ($cols as $c) {
    echo "{$c->Field} ({$c->Type})\n";
}
