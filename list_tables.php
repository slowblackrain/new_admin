<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$tables = Illuminate\Support\Facades\DB::select('SHOW TABLES');
$tableList = [];
foreach ($tables as $t) {
    $tableList[] = current((array)$t);
}

echo json_encode($tableList, JSON_PRETTY_PRINT);
