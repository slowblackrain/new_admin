<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$columns = DB::select("SHOW COLUMNS FROM fm_order_item_option");
foreach ($columns as $col) {
    echo $col->Field . "\n";
}
