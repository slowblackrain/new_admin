<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- fm_goods_info Schema ---\n";
try {
    $columns = DB::select("DESCRIBE fm_goods_info");
    foreach ($columns as $col) {
        echo $col->Field . " (" . $col->Type . ")\n";
    }
} catch (\Exception $e) {
    echo "Table fm_goods_info likely does not exist or error: " . $e->getMessage();
}

echo "\n--- fm_maker? (Manufacturer) ---\n";
try {
    $columns = DB::select("DESCRIBE fm_maker");
    foreach ($columns as $col) {
        echo $col->Field . " (" . $col->Type . ")\n";
    }
} catch (\Exception $e) {
    echo "Table fm_maker likely does not exist.\n";
}

echo "\n--- fm_origin? (Origin) ---\n";
try {
    $columns = DB::select("DESCRIBE fm_origin");
    foreach ($columns as $col) {
        echo $col->Field . " (" . $col->Type . ")\n";
    }
} catch (\Exception $e) {
    echo "Table fm_origin likely does not exist.\n";
}
