<?php
use Illuminate\Support\Facades\DB;
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking fm_config for 'package'...\n";
if (Schema::hasTable('fm_config')) {
    $rows = DB::select("SELECT * FROM fm_config WHERE code LIKE '%package%' OR code LIKE '%wrap%' OR code LIKE '%add%'");
    foreach($rows as $r) print_r($r);

    echo "Checking tax config...\n";
     $rows = DB::select("SELECT * FROM fm_config WHERE code LIKE '%tax%' OR code LIKE '%vat%'");
    foreach($rows as $r) print_r($r);
}

echo "\nChecking fm_zone...\n";
if (Schema::hasTable('fm_zone')) {
    echo "fm_zone exists! Count: " . DB::table('fm_zone')->count() . "\n";
} else {
    echo "fm_zone NOT found.\n";
}
