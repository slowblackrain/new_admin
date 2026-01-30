<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$tableName = isset($argv[1]) ? $argv[1] : 'fm_goods_input';
$columns = DB::select("describe $tableName");
foreach ($columns as $col) {
    echo $col->Field . " (" . $col->Type . ")\n";
}
