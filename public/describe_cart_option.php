<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$kernel->bootstrap();

$columns = Illuminate\Support\Facades\DB::select('SHOW COLUMNS FROM fm_cart_option');
foreach ($columns as $col) {
    echo $col->Field . " | " . $col->Type . "\n";
}
