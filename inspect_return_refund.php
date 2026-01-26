<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Returns Tables:\n";
$tables = DB::select("SHOW TABLES LIKE '%return%'");
foreach ($tables as $t) {
    echo reset($t) . "\n";
}

echo "\nRefund Tables:\n";
$tables2 = DB::select("SHOW TABLES LIKE '%refund%'");
foreach ($tables2 as $t) {
    echo reset($t) . "\n";
}
