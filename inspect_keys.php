<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tables = ['fm_goods', 'fm_goods_option', 'fm_goods_supply'];
$results = [];

foreach ($tables as $table) {
    $item = DB::table($table)->first();
    $results[$table] = $item ? array_keys((array)$item) : [];
}

echo json_encode($results, JSON_PRETTY_PRINT);
