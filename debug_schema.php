<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Schema Inspection ===\n";

// 1. Inspect fm_boardadmin Columns
echo "[fm_boardadmin Columns]\n";
$columns = DB::select('DESCRIBE fm_boardadmin');
$idColumn = '';
foreach($columns as $col) {
    echo "- " . $col->Field . " (" . $col->Type . ")\n";
    if (strpos($col->Field, 'id') !== false && empty($idColumn)) {
        $idColumn = $col->Field; // Guess the ID column
    }
}
echo "Guessed ID Column: " . $idColumn . "\n";

// 2. Inspect fm_boarddata Columns
echo "\n[fm_boarddata Columns]\n";
if (DB::getSchemaBuilder()->hasTable('fm_boarddata')) {
    $dataColumns = DB::select('DESCRIBE fm_boarddata');
    foreach($dataColumns as $col) {
        echo "- " . $col->Field . "\n";
    }
}

// 3. Retry Board Find with correct column
if ($idColumn) {
    echo "\n[Retry Board Search for 'gs_seller_notice']\n";
    $boardConfig = DB::table('fm_boardadmin')->where($idColumn, 'gs_seller_notice')->first();
    if ($boardConfig) {
        echo "Found Board Config!\n";
        echo json_encode($boardConfig) . "\n";
    } else {
        echo "Still not found. Listing first 5 IDs:\n";
        $boards = DB::table('fm_boardadmin')->select($idColumn, 'boardname')->limit(5)->get(); // assuming 'boardname' or similar exists
        foreach($boards as $b) {
            echo " - " . $b->{$idColumn} . "\n";
        }
    }
}

// 4. Provider UserID Check (Repeat from before to be sure)
echo "\n[Provider Check]\n";
$validProvider = DB::table('fm_provider')->where('userid', '!=', '')->first();
if ($validProvider) {
    echo "Valid Provider Found: " . $validProvider->provider_id . " (User: " . $validProvider->userid . ")\n";
} else {
    echo "No provider with userid found.\n";
}
