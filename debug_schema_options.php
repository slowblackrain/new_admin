<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

foreach (['fm_goods_option', 'fm_goods_supply'] as $table) {
    echo "Checking schema for $table using raw SQL...\n";
    $columns = DB::select("SHOW COLUMNS FROM $table");

    foreach ($columns as $col) {
        echo "- " . $col->Field . "\n";
    }
    echo "\n";
}
