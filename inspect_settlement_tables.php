<?php
require_once __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "Tables with 'account':\n";
$tables = DB::select("SHOW TABLES LIKE '%account%'");
foreach ($tables as $t) {
    echo reset($t) . "\n";
}

echo "\nTables with 'calculate':\n";
$tables2 = DB::select("SHOW TABLES LIKE '%calculate%'");
foreach ($tables2 as $t) {
    echo reset($t) . "\n";
}

echo "\nTables with 'tax':\n";
$tables3 = DB::select("SHOW TABLES LIKE '%tax%'");
foreach ($tables3 as $t) {
    echo reset($t) . "\n";
}
