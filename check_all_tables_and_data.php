<?php
require 'vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "--- FULL TABLE LIST ---\n";
$tables = DB::select('SHOW TABLES');
foreach ($tables as $t) {
    $vals = array_values((array)$t);
    echo $vals[0] . "\n";
}

echo "\n--- fm_brand Content (Limit 5) ---\n";
try {
    $brands = DB::table('fm_brand')->limit(5)->get(['id', 'title']);
    foreach($brands as $b) {
        echo "ID: {$b->id}, Title: {$b->title}\n";
    }
} catch(\Exception $e) { echo $e->getMessage(); }

echo "\n--- fm_location Content (Limit 5) ---\n";
try {
    $locs = DB::table('fm_location')->limit(5)->get(['id', 'title', 'location_code']);
    foreach($locs as $l) {
        echo "ID: {$l->id}, Title: {$l->title}, Code: {$l->location_code}\n";
    }
} catch(\Exception $e) { echo $e->getMessage(); }
