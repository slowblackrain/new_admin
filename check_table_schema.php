<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = array_slice($argv, 1);
if (empty($tables)) {
    echo "Usage: php check_table_schema.php [table1] [table2] ...\n";
    exit(1);
}

foreach ($tables as $table) {
    echo "\n--- $table Schema ---\n";
    try {
        $columns = DB::select("DESCRIBE $table");
        foreach ($columns as $col) {
            echo $col->Field . " (" . $col->Type . ")\n";
        }
    } catch (\Exception $e) {
        echo "Error checking $table: " . $e->getMessage() . "\n";
    }
}
