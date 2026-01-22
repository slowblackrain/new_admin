<?php
require 'vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- fm_emoney Schema ---\n";
$columns = DB::select("DESCRIBE fm_emoney");
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

echo "\n--- fm_cash Schema ---\n";
$columns = DB::select("DESCRIBE fm_cash");
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}
