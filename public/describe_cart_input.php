<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);
$columns = Illuminate\Support\Facades\DB::select('describe fm_cart_input');
foreach ($columns as $col) {
    echo "Field: " . $col->Field . " Type: " . $col->Type . "\n";
}
