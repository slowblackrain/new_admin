<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->handle(Illuminate\Http\Request::capture());

use Illuminate\Support\Facades\DB;

$columns = DB::select('SHOW COLUMNS FROM fm_marketing_advertising');
foreach ($columns as $col) {
    echo $col->Field . "\n";
}
