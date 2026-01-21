<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\DB;

echo "Checking schema for fm_goods using raw SQL...\n";
$columns = DB::select('SHOW COLUMNS FROM fm_goods');

foreach ($columns as $col) {
    echo "- " . $col->Field . "\n";
}
