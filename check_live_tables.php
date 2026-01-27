<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    echo "Checking Tables in Live DB...\n";
    $tables = \Illuminate\Support\Facades\DB::select("SHOW TABLES LIKE 'fm_%location%'");
    foreach ($tables as $t) {
        // The property name depends on the string 'Tables_in_dbname'
        $vals = array_values((array)$t);
        echo "Found: " . $vals[0] . "\n";
    }
    
    echo "\nChecking 'fm_scm_location_link' existence...\n";
    try {
        \Illuminate\Support\Facades\DB::table('fm_scm_location_link')->first();
        echo "fm_scm_location_link EXISTS and is readable.\n";
    } catch (\Exception $e) {
        echo "fm_scm_location_link ERROR: " . $e->getMessage() . "\n";
    }

} catch (\Exception $e) {
    echo "Overall Connection failed: " . $e->getMessage() . "\n";
}
