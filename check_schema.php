<?php
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function describeTable($table) {
    echo "\n--- Schema: $table ---\n";
    try {
        $columns = \Illuminate\Support\Facades\DB::select("DESCRIBE $table");
        foreach ($columns as $col) {
            echo "{$col->Field} ({$col->Type})\n";
        }
    } catch (\Exception $e) {
        echo "Error: " . $e->getMessage() . "\n";
    }
}

describeTable('fm_member');
