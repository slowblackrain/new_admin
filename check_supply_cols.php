<?php

use Illuminate\Support\Facades\DB;

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

echo "--- fm_goods_option ---\n";
$columns = DB::select("SHOW COLUMNS FROM fm_goods_option");
foreach ($columns as $col) echo $col->Field . " ";
echo "\n\n--- fm_goods_supply ---\n";
$columns = DB::select("SHOW COLUMNS FROM fm_goods_supply");
foreach ($columns as $col) echo $col->Field . " ";
