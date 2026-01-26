<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "fm_order_item Columns:\n";
$cols = DB::select("SHOW COLUMNS FROM fm_order_item");
foreach($cols as $c) {
    echo $c->Field . " | " . $c->Type . "\n";
}

echo "\nChecking if fm_order_item_option exists:\n";
$tables = DB::select("SHOW TABLES LIKE 'fm_order_item_option'");
if ($tables) {
    echo "Exists.\n";
    $cols2 = DB::select("SHOW COLUMNS FROM fm_order_item_option");
    foreach($cols2 as $c) {
        echo $c->Field . " | " . $c->Type . "\n";
    }
} else {
    echo "Does NOT exist.\n";
}
