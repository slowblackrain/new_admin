<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

function inspect($table)
{
    echo "TABLE: $table" . PHP_EOL;
    $columns = \Illuminate\Support\Facades\DB::select("SHOW COLUMNS FROM $table");
    foreach ($columns as $col) {
        echo " - " . $col->Field . " (" . $col->Type . ")" . PHP_EOL;
    }
    echo PHP_EOL;
}

inspect('fm_boardmanager');
inspect('fm_boarddata');
