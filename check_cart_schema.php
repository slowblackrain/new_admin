<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "=== fm_cart ===\n";
    $columns = DB::select("DESCRIBE fm_cart");
    foreach ($columns as $col) {
        echo "{$col->Field} ({$col->Type})\n";
    }

    echo "\n=== fm_cart_option ===\n";
    $columns = DB::select("DESCRIBE fm_cart_option");
    foreach ($columns as $col) {
        echo "{$col->Field} ({$col->Type})\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
