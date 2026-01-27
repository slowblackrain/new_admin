<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

$tables = ['fm_order', 'fm_order_item', 'fm_order_item_option', 'fm_order_sequence'];

foreach ($tables as $table) {
    if (Schema::hasTable($table)) {
        echo "Table '{$table}' exists.\n";
        // Optionally describe to verify columns
        // $cols = Schema::getColumnListing($table);
        // echo "Cols: " . implode(', ', $cols) . "\n";
    } else {
        echo "Table '{$table}' DOES NOT EXIST.\n";
    }
}

// Check fm_order_sequence structure
if (Schema::hasTable('fm_order_sequence')) {
    $cols = DB::select("DESCRIBE fm_order_sequence");
    foreach ($cols as $c) {
        echo "fm_order_sequence col: {$c->Field} ({$c->Type})\n";
    }
}
