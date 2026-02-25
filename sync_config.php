<?php
// sync_config.php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    echo "Connecting to remote DB (49.247.170.176)...\n";
    $remote = new PDO("mysql:host=49.247.170.176;dbname=dometopia;charset=utf8", "dometopia", "11dnjf7dlf!!");
    $remote->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $remote->query("SELECT * FROM fm_config");
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo "Found " . count($rows) . " rows in remote DB.\n";
    
    if (count($rows) > 0) {
        echo "Truncating local fm_config table...\n";
        DB::table('fm_config')->truncate();
        
        echo "Inserting into local DB...\n";
        $chunks = array_chunk($rows, 100);
        foreach ($chunks as $chunk) {
            DB::table('fm_config')->insert($chunk);
        }
        
        echo "Successfully synced " . count($rows) . " rows to local DB.\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
