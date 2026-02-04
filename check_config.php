<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "\n--- QUERYING PG CONFIG ---\n";
$results = Illuminate\Support\Facades\DB::select("SELECT * FROM fm_config WHERE groupcd LIKE '%toss%' OR groupcd LIKE '%pairing%' OR groupcd LIKE '%cker%' OR groupcd='system' or groupcd='pg'");

if (count($results) > 0) {
    echo "Found " . count($results) . " rows.\n";
    foreach ($results as $row) {
        print_r($row);
    }
} else {
    echo "No relevant PG config found in fm_config table.\n";
}
