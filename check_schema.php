<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);

$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "--- fm_emoney Columns ---\n";
    $columns = DB::select('SHOW COLUMNS FROM fm_emoney');
    foreach ($columns as $col) {
        echo $col->Field . " (" . $col->Type . ")\n";
    }

    echo "\n--- fm_point Columns ---\n";
    $columns = DB::select('SHOW COLUMNS FROM fm_point');
    foreach ($columns as $col) {
        echo $col->Field . " (" . $col->Type . ")\n";
    }
    
    echo "\n--- fm_download Columns ---\n";
    $columns = DB::select('SHOW COLUMNS FROM fm_download');
    foreach ($columns as $col) {
        echo $col->Field . " (" . $col->Type . ")\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
