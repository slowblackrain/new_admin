<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$tables = ['fm_cart', 'fm_cart_option'];

foreach ($tables as $table) {
    echo "TABLE: $table\n";
    $columns = DB::select("SHOW COLUMNS FROM $table");
    foreach ($columns as $col) {
        echo " - " . $col->Field . "\n";
    }
    echo "\n";
}
