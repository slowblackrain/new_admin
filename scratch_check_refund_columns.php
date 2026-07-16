<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$cols = DB::select("DESCRIBE fm_order_refund");
echo "COLUMNS:\n";
foreach($cols as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}

$row = DB::table('fm_order_refund')->first();
echo "\nSAMPLE ROW:\n";
print_r($row);
