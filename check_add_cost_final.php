<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking tables...\n";
$tables = DB::select("SHOW TABLES LIKE 'fm_delivery%'");
foreach ($tables as $t) {
    foreach ($t as $k => $v) echo $v . "\n";
}

echo "\nChecking fm_delivery_add_price content...\n";
if (count(DB::select("SHOW TABLES LIKE 'fm_delivery_add_price'")) > 0) {
    $rows = DB::select("SELECT * FROM fm_delivery_add_price LIMIT 5");
    if (empty($rows)) echo "Table empty.\n";
    else print_r($rows);
} else {
    echo "fm_delivery_add_price NOT found.\n";
}
