<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    // Check if fm_config exists and what's in it
    $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES LIKE "fm_config"');
    if (count($tables) > 0) {
        echo "Table fm_config exists.\n";
        $columns = \Illuminate\Support\Facades\DB::select('DESCRIBE fm_config');
        foreach ($columns as $col) {
            echo $col->Field . " (" . $col->Type . ")\n";
        }

        // Try to fetch a few rows to see structure
        $rows = \Illuminate\Support\Facades\DB::table('fm_config')->take(5)->get();
        print_r($rows);
    } else {
        echo "Table fm_config does not exist.\n";
    }

    // Check for any table with 'privacy' or 'agreement' in the name
    $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES LIKE "%privacy%"');
    foreach ($tables as $table) {
        foreach ($table as $val)
            echo "Found table: $val\n";
    }
    $tables = \Illuminate\Support\Facades\DB::select('SHOW TABLES LIKE "%agreement%"');
    foreach ($tables as $table) {
        foreach ($table as $val)
            echo "Found table: $val\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
