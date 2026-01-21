<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Checking fm_manager table...\n";
try {
    $columns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM fm_manager");
    foreach ($columns as $col) {
        echo $col->Field . " (" . $col->Type . ")\n";
    }

    echo "\nChecking first 5 managers...\n";
    $managers = \Illuminate\Support\Facades\DB::table('fm_manager')->limit(5)->get();
    foreach ($managers as $mgr) {
        echo "ID: {$mgr->manager_id} | Name: {$mgr->mname} | Seq: {$mgr->manager_seq}\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
