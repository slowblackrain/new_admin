<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['fm_brand', 'fm_brand_link', 'fm_location', 'fm_location_link'];

foreach ($tables as $t) {
    try {
        echo "\n--- $t ---\n";
        $columns = DB::select("DESCRIBE $t");
        foreach ($columns as $col) {
            echo $col->Field . " (" . $col->Type . ")\n";
        }
    } catch (\Exception $e) {
        echo "Table $t likely does not exist.\n";
    }
}
